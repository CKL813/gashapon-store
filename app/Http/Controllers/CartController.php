<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    /** Render the cart page. Cart data lives in localStorage — no server state needed. */
    public function index(): Response
    {
        return Inertia::render('Cart');
    }

    /**
     * Validate and apply a coupon code.
     *
     * Accepts: { code: string, subtotal: number }
     * Returns: coupon details with calculated discount, or 422 on failure.
     */
    public function applyCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'code'     => ['required', 'string', 'max:50'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        $coupon = Coupon::active()
            ->where('code', strtoupper(trim($request->code)))
            ->first();

        if (! $coupon || ! $coupon->isValid((string) $request->subtotal)) {
            $message = match (true) {
                ! $coupon                => 'Coupon code not found.',
                ! $coupon->is_active     => 'This coupon is no longer active.',
                $coupon->isExpired()     => 'This coupon has expired.',
                $coupon->isExhausted()   => 'This coupon has reached its usage limit.',
                default                  => 'This coupon cannot be applied to your order.',
            };

            return response()->json(['message' => $message], 422);
        }

        $discount = $coupon->calculateDiscount((string) $request->subtotal);

        return response()->json([
            'coupon' => [
                'id'       => $coupon->id,
                'code'     => $coupon->code,
                'type'     => $coupon->type->value,
                'value'    => (float) $coupon->value,
                'discount' => (float) $discount,
            ],
        ]);
    }
}
