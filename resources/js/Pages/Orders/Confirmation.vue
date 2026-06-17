<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { Order } from '@/types';

// ── Props ─────────────────────────────────────────────────────────────────────

interface Props {
    order: Order;
    paymentStatus: string;
}

const props = defineProps<Props>();

// ── Helpers ───────────────────────────────────────────────────────────────────

const isSuccess = props.paymentStatus === 'succeeded';

function fmt(n: number): string {
    return n.toFixed(2);
}

function formatDate(iso: string): string {
    return new Intl.DateTimeFormat('en-HK', {
        year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    }).format(new Date(iso));
}
</script>

<template>
    <StorefrontLayout>
        <Head :title="isSuccess ? 'Order Confirmed!' : 'Order Placed'" />

        <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">

            <!-- Success / Pending header -->
            <div class="mb-8 rounded-2xl border p-8 text-center shadow-sm"
                 :class="isSuccess ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50'">
                <div class="mb-3 text-6xl">{{ isSuccess ? '🎉' : '⏳' }}</div>
                <h1 class="text-2xl font-extrabold" :class="isSuccess ? 'text-emerald-800' : 'text-amber-800'">
                    {{ isSuccess ? 'Order Confirmed!' : 'Order Received' }}
                </h1>
                <p class="mt-2 text-sm" :class="isSuccess ? 'text-emerald-700' : 'text-amber-700'">
                    <template v-if="isSuccess">
                        Payment successful! Your capsule toys are on their way. 📦
                    </template>
                    <template v-else>
                        Your order is placed. We'll confirm once payment is verified.
                    </template>
                </p>
                <div class="mt-4 inline-block rounded-full bg-white px-4 py-1.5 text-sm font-semibold shadow-sm"
                     :class="isSuccess ? 'text-emerald-700' : 'text-amber-700'">
                    Order #{{ order.id }}
                </div>
            </div>

            <div class="space-y-5">

                <!-- Order details card -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-sm font-bold uppercase tracking-wide text-slate-500">Order Details</h2>

                    <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div>
                            <dt class="text-slate-500">Order date</dt>
                            <dd class="font-medium text-slate-900">{{ formatDate(order.created_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Status</dt>
                            <dd>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                                      :class="{
                                          'bg-emerald-100 text-emerald-700': order.status === 'processing',
                                          'bg-amber-100 text-amber-700': order.status === 'pending',
                                          'bg-blue-100 text-blue-700': order.status === 'shipped',
                                          'bg-slate-100 text-slate-600': ['delivered', 'cancelled', 'refunded'].includes(order.status),
                                      }">
                                    {{ order.status }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Customer</dt>
                            <dd class="font-medium text-slate-900">{{ order.customer_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Email</dt>
                            <dd class="font-medium text-slate-900">{{ order.customer_email }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Shipping address -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">Shipping Address</h2>
                    <address class="not-italic text-sm text-slate-700 leading-relaxed">
                        <strong>{{ order.shipping_address.name }}</strong><br />
                        {{ order.shipping_address.address_line_1 }}<br />
                        <template v-if="order.shipping_address.address_line_2">
                            {{ order.shipping_address.address_line_2 }}<br />
                        </template>
                        {{ order.shipping_address.district }}, {{ order.shipping_address.city }}
                        <span v-if="order.shipping_address.phone" class="block mt-1 text-slate-500">
                            📞 {{ order.shipping_address.phone }}
                        </span>
                    </address>
                </div>

                <!-- Order items -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Items Ordered</h2>
                    </div>
                    <ul class="divide-y divide-slate-100">
                        <li v-for="item in order.items" :key="item.id"
                            class="flex items-center gap-4 px-6 py-4">
                            <img v-if="item.snapshot.image_url"
                                 :src="item.snapshot.image_url"
                                 :alt="item.product_name"
                                 class="h-14 w-14 rounded-xl object-cover shrink-0" />
                            <div v-else class="flex h-14 w-14 items-center justify-center rounded-xl bg-slate-100 text-2xl shrink-0">🎲</div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-900 truncate">{{ item.product_name }}</p>
                                <p v-if="item.snapshot.variant_name" class="text-xs text-slate-500">
                                    {{ item.snapshot.variant_name }}
                                </p>
                                <p class="text-xs text-slate-400">SKU: {{ item.product_sku || '—' }}</p>
                            </div>

                            <div class="text-right shrink-0">
                                <p class="text-sm font-semibold text-slate-900">${{ fmt(item.total_price) }}</p>
                                <p class="text-xs text-slate-400">{{ item.quantity }} × ${{ fmt(item.unit_price) }}</p>
                            </div>
                        </li>
                    </ul>

                    <!-- Totals -->
                    <div class="border-t border-slate-100 bg-slate-50 px-6 py-4 space-y-2">
                        <div class="flex justify-between text-sm text-slate-600">
                            <span>Subtotal</span>
                            <span>${{ fmt(order.subtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-slate-600">
                            <span>Shipping</span>
                            <span>{{ order.shipping_cost === 0 ? 'Free' : `$${fmt(order.shipping_cost)}` }}</span>
                        </div>
                        <div v-if="order.discount > 0" class="flex justify-between text-sm text-emerald-600">
                            <span>Discount{{ order.coupon_code ? ` (${order.coupon_code})` : '' }}</span>
                            <span>−${{ fmt(order.discount) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-slate-200 pt-3 font-bold text-slate-900">
                            <span>Total</span>
                            <span class="text-orange-600">${{ fmt(order.total) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="order.notes" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-2 text-sm font-bold uppercase tracking-wide text-slate-500">Order Notes</h2>
                    <p class="text-sm text-slate-700">{{ order.notes }}</p>
                </div>

                <!-- Actions -->
                <div class="flex flex-col gap-3 sm:flex-row">
                    <Link :href="route('products.index')"
                          class="flex-1 rounded-xl bg-orange-500 py-3.5 text-center font-semibold text-white transition hover:bg-orange-600">
                        Continue Shopping
                    </Link>
                    <Link :href="route('home')"
                          class="flex-1 rounded-xl border border-slate-200 bg-white py-3.5 text-center font-semibold text-slate-700 transition hover:bg-slate-50">
                        Back to Home
                    </Link>
                </div>

            </div>
        </div>
    </StorefrontLayout>
</template>
