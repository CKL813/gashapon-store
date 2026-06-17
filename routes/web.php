<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

// ── Storefront ────────────────────────────────────────────────────────────────
Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

// ── Cart ──────────────────────────────────────────────────────────────────────
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon');

// ── Checkout ──────────────────────────────────────────────────────────────────
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

// ── Orders ────────────────────────────────────────────────────────────────────
Route::get('/orders/{order}/confirmation', [OrderController::class, 'confirmation'])
    ->name('orders.confirmation');

// ── Stripe Webhook (excluded from CSRF) ───────────────────────────────────────
Route::post('/stripe/webhook', [PaymentController::class, 'webhook'])
    ->name('stripe.webhook')
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

// ── Auth / Profile ────────────────────────────────────────────────────────────
Route::get('/dashboard', fn () => inertia('Dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
