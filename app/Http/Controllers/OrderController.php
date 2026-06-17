<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Show the order confirmation page after a successful Stripe redirect.
     *
     * Accessible by:
     *   - Authenticated users who own the order
     *   - Guests who have this order stored in their session (pending_order_id)
     */
    public function confirmation(Order $order): Response|RedirectResponse
    {
        $user = auth()->user();

        $isOwner  = $user && $order->user_id === $user->id;
        $isGuest  = ! $user && session('pending_order_id') === $order->id;
        $isAdmin  = $user && $user->hasRole('admin');

        if (! $isOwner && ! $isGuest && ! $isAdmin) {
            abort(403);
        }

        $order->load('items', 'coupon');

        return Inertia::render('Orders/Confirmation', [
            'order' => [
                'id'              => $order->id,
                'status'          => $order->status->value,
                'customer_name'   => $order->customer_name,
                'customer_email'  => $order->customer_email,
                'shipping_address'=> $order->shipping_address,
                'subtotal'        => (float) $order->subtotal,
                'shipping_cost'   => (float) $order->shipping_cost,
                'discount'        => (float) $order->discount,
                'total'           => (float) $order->total,
                'coupon_code'     => $order->coupon?->code,
                'notes'           => $order->notes,
                'created_at'      => $order->created_at->toIso8601String(),
                'items'           => $order->items->map(fn ($item) => [
                    'id'           => $item->id,
                    'quantity'     => $item->quantity,
                    'unit_price'   => (float) $item->unit_price,
                    'total_price'  => (float) $item->total_price,
                    'product_name' => $item->product_name,
                    'product_sku'  => $item->product_sku,
                    'snapshot'     => $item->product_snapshot,
                ]),
            ],
            // Stripe query params for displaying payment status
            'paymentStatus' => request('redirect_status', 'succeeded'),
        ]);
    }
}
