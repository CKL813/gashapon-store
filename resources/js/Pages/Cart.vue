<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import { useCartStore } from '@/stores/cart';

const cart = useCartStore();

function fmt(amount: number): string {
    return amount.toFixed(2);
}
</script>

<template>
    <StorefrontLayout>
        <Head title="Shopping Cart" />

        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

            <!-- Page title -->
            <h1 class="mb-8 text-2xl font-extrabold text-slate-900">Shopping Cart</h1>

            <!-- Empty state -->
            <div v-if="cart.items.length === 0" class="flex flex-col items-center justify-center py-24 text-center">
                <div class="mb-4 text-7xl">🛒</div>
                <h2 class="mb-2 text-xl font-semibold text-slate-700">Your cart is empty</h2>
                <p class="mb-6 text-slate-500">Add some capsule toys and come back!</p>
                <Link :href="route('products.index')"
                      class="rounded-xl bg-orange-500 px-6 py-3 font-semibold text-white transition hover:bg-orange-600">
                    Browse Products
                </Link>
            </div>

            <!-- Cart with items -->
            <div v-else class="grid grid-cols-1 gap-8 lg:grid-cols-3">

                <!-- Items column (spans 2 on large screens) -->
                <div class="lg:col-span-2">
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                        <ul class="divide-y divide-slate-100">
                            <li v-for="item in cart.items"
                                :key="`${item.product.id}-${item.variant?.id ?? 'base'}`"
                                class="flex items-center gap-4 p-4 sm:p-5">

                                <!-- Product image -->
                                <Link :href="route('products.show', item.product.slug)" class="shrink-0">
                                    <img v-if="item.product.image_url"
                                         :src="item.product.image_url"
                                         :alt="item.product.name"
                                         class="h-20 w-20 rounded-xl object-cover transition hover:opacity-90" />
                                    <div v-else
                                         class="flex h-20 w-20 items-center justify-center rounded-xl bg-slate-100 text-3xl">
                                        🎲
                                    </div>
                                </Link>

                                <!-- Details -->
                                <div class="min-w-0 flex-1">
                                    <Link :href="route('products.show', item.product.slug)"
                                          class="block truncate text-sm font-semibold text-slate-900 hover:text-orange-600">
                                        {{ item.product.name }}
                                    </Link>
                                    <p v-if="item.variant" class="mt-0.5 text-xs text-slate-500">
                                        {{ item.variant.name }}
                                    </p>
                                    <p class="mt-1 text-sm font-bold text-orange-600">
                                        ${{ fmt(item.variant?.price ?? item.product.price) }} each
                                    </p>
                                </div>

                                <!-- Quantity controls -->
                                <div class="flex items-center gap-1 rounded-lg border border-slate-200 p-1">
                                    <button
                                        @click="cart.updateQty(item.product.id, item.variant?.id, item.quantity - 1)"
                                        :disabled="item.quantity <= 1"
                                        class="flex h-7 w-7 items-center justify-center rounded text-slate-600 transition hover:bg-orange-50 hover:text-orange-600 disabled:cursor-not-allowed disabled:opacity-40">
                                        −
                                    </button>
                                    <span class="w-8 text-center text-sm font-medium">{{ item.quantity }}</span>
                                    <button
                                        @click="cart.updateQty(item.product.id, item.variant?.id, item.quantity + 1)"
                                        class="flex h-7 w-7 items-center justify-center rounded text-slate-600 transition hover:bg-orange-50 hover:text-orange-600">
                                        +
                                    </button>
                                </div>

                                <!-- Line total -->
                                <div class="w-20 text-right text-sm font-semibold text-slate-900">
                                    ${{ fmt((item.variant?.price ?? item.product.price) * item.quantity) }}
                                </div>

                                <!-- Remove -->
                                <button @click="cart.remove(item.product.id, item.variant?.id)"
                                        class="shrink-0 rounded-lg p-1.5 text-slate-400 transition hover:bg-red-50 hover:text-red-500"
                                        title="Remove item">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </li>
                        </ul>

                        <!-- Clear cart -->
                        <div class="flex justify-end border-t border-slate-100 px-5 py-3">
                            <button @click="cart.clear()"
                                    class="text-xs text-slate-400 transition hover:text-red-500 hover:underline">
                                Clear cart
                            </button>
                        </div>
                    </div>

                    <!-- Continue shopping -->
                    <div class="mt-4">
                        <Link :href="route('products.index')"
                              class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-orange-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Continue Shopping
                        </Link>
                    </div>
                </div>

                <!-- Order summary sidebar -->
                <div class="lg:col-span-1">
                    <div class="sticky top-24 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="mb-5 text-lg font-bold text-slate-900">Order Summary</h2>

                        <!-- Line items summary -->
                        <ul class="mb-4 space-y-2 text-sm">
                            <li v-for="item in cart.items" :key="`sum-${item.product.id}-${item.variant?.id}`"
                                class="flex justify-between text-slate-600">
                                <span class="truncate pr-2">{{ item.product.name }}
                                    <span v-if="item.variant" class="text-slate-400">· {{ item.variant.name }}</span>
                                    × {{ item.quantity }}
                                </span>
                                <span class="shrink-0 font-medium text-slate-800">
                                    ${{ fmt((item.variant?.price ?? item.product.price) * item.quantity) }}
                                </span>
                            </li>
                        </ul>

                        <div class="space-y-2 border-t border-slate-100 pt-4">
                            <div class="flex justify-between text-sm text-slate-600">
                                <span>Subtotal ({{ cart.count }} items)</span>
                                <span>${{ fmt(cart.total) }}</span>
                            </div>
                            <div class="flex justify-between text-xs text-slate-400">
                                <span>Shipping</span>
                                <span>Calculated at checkout</span>
                            </div>
                            <div class="flex justify-between text-xs text-slate-400">
                                <span>Coupon / Discount</span>
                                <span>Applied at checkout</span>
                            </div>
                        </div>

                        <div class="mt-4 border-t border-slate-100 pt-4">
                            <div class="flex justify-between text-base font-bold text-slate-900">
                                <span>Estimated Total</span>
                                <span class="text-orange-600">${{ fmt(cart.total) }}</span>
                            </div>
                        </div>

                        <Link :href="route('checkout.index')"
                              class="mt-6 block w-full rounded-xl bg-orange-500 py-3.5 text-center text-sm font-semibold text-white shadow transition hover:bg-orange-600 active:scale-[0.98]">
                            Proceed to Checkout →
                        </Link>

                        <!-- Trust badges -->
                        <div class="mt-4 flex items-center justify-center gap-4 text-xs text-slate-400">
                            <span class="flex items-center gap-1">🔒 Secure checkout</span>
                            <span class="flex items-center gap-1">📦 Fast shipping</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </StorefrontLayout>
</template>
