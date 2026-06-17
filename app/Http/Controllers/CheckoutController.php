<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    /** Render the checkout page with shipping districts and Stripe public key. */
    public function index(): Response
    {
        return Inertia::render('Checkout', [
            'shippingDistricts' => ShippingRate::orderBy('district')
                ->get(['district', 'rate'])
                ->map(fn ($r) => ['district' => $r->district, 'rate' => (float) $r->rate])
                ->toArray(),
            'stripePublicKey' => config('services.stripe.public_key', ''),
        ]);
    }

    /**
     * Create an order and a Stripe PaymentIntent.
     *
     * Returns: { order_id: int, client_secret: string }
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $order = DB::transaction(function () use ($validated, $request) {
                return $this->createOrder($validated, $request->user());
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        // Create Stripe PaymentIntent outside the DB transaction
        try {
            $clientSecret = $this->createPaymentIntent($order);
        } catch (ApiErrorException $e) {
            // Order created but payment setup failed — return order ID so we can retry
            return response()->json([
                'message'  => 'Payment service unavailable. Please try again.',
                'order_id' => $order->id,
            ], 503);
        }

        // Persist the PaymentIntent ID for webhook reconciliation
        $order->update([
            'stripe_payment_intent_id' => $order->fresh()->stripe_payment_intent_id,
        ]);

        // Store order ID in session so the confirmation page can authorise guests
        session(['pending_order_id' => $order->id]);

        return response()->json([
            'order_id'      => $order->id,
            'client_secret' => $clientSecret,
        ]);
    }

    // ── Private Helpers ───────────────────────────────────────────────────────

    /** Build and persist the Order + OrderItems inside a transaction. */
    private function createOrder(array $data, ?object $user): Order
    {
        $subtotal   = '0.00';
        $orderItems = [];

        foreach ($data['items'] as $cartItem) {
            /** @var Product $product */
            $product = Product::active()
                ->lockForUpdate()
                ->findOrFail($cartItem['product_id']);

            $variant = isset($cartItem['variant_id'])
                ? ProductVariant::lockForUpdate()->findOrFail($cartItem['variant_id'])
                : null;

            $qty           = (int) $cartItem['quantity'];
            $availableStock = $variant ? $variant->stock : $product->stock;

            if ($availableStock < $qty) {
                throw new \Exception(
                    "Sorry, \"{$product->name}\"" .
                    ($variant ? " ({$variant->name})" : '') .
                    " only has {$availableStock} left in stock."
                );
            }

            // Use server-side price — never trust the client
            $unitPrice = $variant
                ? (float) ($variant->price ?? $product->priceFor($user))
                : (float) $product->priceFor($user);

            $lineTotal = round($unitPrice * $qty, 2);
            $subtotal  = (string) round((float) $subtotal + $lineTotal, 2);

            // Deduct stock
            if ($variant) {
                $variant->decrement('stock', $qty);
            } else {
                $product->decrement('stock', $qty);
            }

            $orderItems[] = [
                'product_id'         => $product->id,
                'product_variant_id' => $variant?->id,
                'quantity'           => $qty,
                'unit_price'         => $unitPrice,
                'total_price'        => $lineTotal,
                'product_snapshot'   => [
                    'id'           => $product->id,
                    'name'         => $product->name,
                    'sku'          => $variant?->sku ?? $product->sku,
                    'price'        => $unitPrice,
                    'variant_name' => $variant?->name,
                    'image_url'    => $product->getFirstMediaUrl('images', 'thumb') ?: null,
                ],
            ];
        }

        // Apply coupon
        $discount = 0.0;
        $coupon   = null;
        if (! empty($data['coupon_code'])) {
            $coupon = Coupon::active()
                ->where('code', strtoupper(trim($data['coupon_code'])))
                ->first();

            if ($coupon && $coupon->isValid($subtotal)) {
                $discount = (float) $coupon->calculateDiscount($subtotal);
                $coupon->increment('used_count');
            }
        }

        // Shipping cost
        $shippingCost = (float) ShippingRate::rateForDistrict($data['shipping']['district']);

        // Total = subtotal + shipping - discount, minimum 0
        $total = max(0, round((float) $subtotal + $shippingCost - $discount, 2));

        // Build address payloads
        $shippingAddress = array_merge($data['shipping'], [
            'name'  => $data['contact']['name'],
            'phone' => $data['contact']['phone'] ?? null,
        ]);

        $billingAddress = ($data['billing_same'] ?? true)
            ? $shippingAddress
            : array_merge($data['billing'] ?? [], ['name' => $data['contact']['name']]);

        $order = Order::create([
            'user_id'          => $user?->id,
            'coupon_id'        => $coupon?->id,
            'status'           => OrderStatus::Pending,
            'shipping_address' => $shippingAddress,
            'billing_address'  => $billingAddress,
            'subtotal'         => $subtotal,
            'shipping_cost'    => $shippingCost,
            'discount'         => $discount,
            'total'            => $total,
            'guest_name'       => $user ? null : $data['contact']['name'],
            'guest_email'      => $user ? null : ($data['contact']['email'] ?? null),
            'notes'            => $data['notes'] ?? null,
        ]);

        $order->items()->createMany($orderItems);

        return $order;
    }

    /** Create a Stripe PaymentIntent and store the ID on the order. */
    private function createPaymentIntent(Order $order): string
    {
        Stripe::setApiKey(config('services.stripe.secret_key'));

        $intent = PaymentIntent::create([
            'amount'                    => (int) round((float) $order->total * 100), // cents
            'currency'                  => 'hkd',
            'automatic_payment_methods' => ['enabled' => true],
            'metadata'                  => [
                'order_id'    => $order->id,
                'customer'    => $order->customer_name,
                'order_total' => $order->total,
            ],
        ]);

        $order->update(['stripe_payment_intent_id' => $intent->id]);

        return $intent->client_secret;
    }
}
