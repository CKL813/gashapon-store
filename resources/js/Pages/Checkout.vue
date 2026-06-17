<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, nextTick, ref, watch } from 'vue';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import { useCartStore } from '@/stores/cart';
import type { AppliedCoupon, CheckoutForm, PageProps, ShippingDistrict } from '@/types';

// ── Props ─────────────────────────────────────────────────────────────────────

interface Props {
    shippingDistricts: ShippingDistrict[];
    stripePublicKey: string;
}

const props = defineProps<Props>();

// ── State ─────────────────────────────────────────────────────────────────────

const cart  = useCartStore();
const page  = usePage<PageProps>();
const user  = computed(() => page.props.auth.user);

/** 'form' → fill details; 'payment' → Stripe Element; 'processing' → submitting */
const phase = ref<'form' | 'payment' | 'processing'>('form');

const form = ref<CheckoutForm>({
    contact: {
        name:  user.value?.name  ?? '',
        email: user.value?.email ?? '',
        phone: '',
    },
    shipping: {
        address_line_1: '',
        address_line_2: '',
        district:       '',
        city:           '',
    },
    billing_same: true,
    billing: {
        address_line_1: '',
        address_line_2: '',
        district:       '',
        city:           '',
    },
    coupon_code: '',
    notes: '',
});

const errors         = ref<Record<string, string>>({});
const serverError    = ref<string | null>(null);
const stripeError    = ref<string | null>(null);
const couponLoading  = ref(false);
const couponError    = ref<string | null>(null);
const appliedCoupon  = ref<AppliedCoupon | null>(null);
const orderId        = ref<number | null>(null);

// ── Computed Totals ───────────────────────────────────────────────────────────

const subtotal = computed(() => cart.total);

const shippingCost = computed(() => {
    if (! form.value.shipping.district) return 0;
    const found = props.shippingDistricts.find(
        d => d.district === form.value.shipping.district,
    );
    return found ? found.rate : 0;
});

const discount = computed(() => appliedCoupon.value?.discount ?? 0);

const total = computed(() =>
    Math.max(0, subtotal.value + shippingCost.value - discount.value),
);

// ── Coupon ────────────────────────────────────────────────────────────────────

async function applyCoupon(): Promise<void> {
    const code = form.value.coupon_code.trim();
    if (! code) return;

    couponError.value   = null;
    appliedCoupon.value = null;
    couponLoading.value = true;

    try {
        const { data } = await axios.post<{ coupon: AppliedCoupon }>(
            route('cart.coupon'),
            { code, subtotal: subtotal.value },
        );
        appliedCoupon.value = data.coupon;
    } catch (err: any) {
        couponError.value = err.response?.data?.message ?? 'Invalid coupon code.';
    } finally {
        couponLoading.value = false;
    }
}

function removeCoupon(): void {
    appliedCoupon.value  = null;
    couponError.value    = null;
    form.value.coupon_code = '';
}

// Reset applied coupon when district changes (shipping cost affects coupon validity)
watch(() => form.value.shipping.district, () => {
    if (appliedCoupon.value) removeCoupon();
});

// ── Order Submission ──────────────────────────────────────────────────────────

let stripeInstance: any = null;
let elementsInstance:  any = null;

async function submitOrder(): Promise<void> {
    errors.value      = {};
    serverError.value = null;
    phase.value       = 'processing';

    const payload = {
        items: cart.items.map(i => ({
            product_id: i.product.id,
            variant_id: i.variant?.id ?? null,
            quantity:   i.quantity,
        })),
        contact:      form.value.contact,
        shipping:     form.value.shipping,
        billing_same: form.value.billing_same,
        billing:      form.value.billing_same ? null : form.value.billing,
        coupon_code:  appliedCoupon.value?.code ?? form.value.coupon_code ?? null,
        notes:        form.value.notes || null,
    };

    try {
        const { data } = await axios.post<{ order_id: number; client_secret: string }>(
            route('checkout.store'),
            payload,
        );

        orderId.value = data.order_id;
        phase.value   = 'payment';

        await nextTick();
        await initStripe(data.client_secret);

    } catch (err: any) {
        phase.value = 'form';

        if (err.response?.status === 422) {
            const responseErrors = err.response.data.errors ?? {};
            // Flatten nested validation keys (e.g. "contact.name" → "contact_name")
            errors.value = Object.fromEntries(
                Object.entries(responseErrors).map(([k, v]) => [
                    k.replace(/\./g, '_'),
                    Array.isArray(v) ? (v[0] as string) : (v as string),
                ]),
            );
        } else {
            serverError.value = err.response?.data?.message ?? 'Something went wrong. Please try again.';
        }
    }
}

// ── Stripe Payment Element ────────────────────────────────────────────────────

async function initStripe(clientSecret: string): Promise<void> {
    if (! props.stripePublicKey) {
        stripeError.value = 'Payment is not configured. Please contact support.';
        return;
    }

    const { loadStripe } = await import('@stripe/stripe-js');
    stripeInstance = await loadStripe(props.stripePublicKey);

    if (! stripeInstance) {
        stripeError.value = 'Failed to load payment provider.';
        return;
    }

    elementsInstance = stripeInstance.elements({ clientSecret });
    const paymentEl  = elementsInstance.create('payment', {
        layout: 'tabs',
    });
    paymentEl.mount('#stripe-payment-element');
}

async function confirmPayment(): Promise<void> {
    if (! stripeInstance || ! elementsInstance) return;

    stripeError.value = null;
    phase.value       = 'processing';

    const { error } = await stripeInstance.confirmPayment({
        elements: elementsInstance,
        confirmParams: {
            return_url: `${window.location.origin}/orders/${orderId.value}/confirmation`,
        },
    });

    if (error) {
        // confirmPayment only rejects synchronously on immediate errors;
        // successful payments redirect away automatically.
        stripeError.value = error.message ?? 'Payment failed. Please try again.';
        phase.value       = 'payment';
    }
}

// ── Helpers ───────────────────────────────────────────────────────────────────

function fmt(n: number): string {
    return n.toFixed(2);
}

function fieldError(key: string): string | undefined {
    return errors.value[key];
}
</script>

<template>
    <StorefrontLayout>
        <Head title="Checkout" />

        <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">

            <!-- Empty cart guard -->
            <div v-if="cart.items.length === 0 && phase === 'form'"
                 class="flex flex-col items-center justify-center py-24 text-center">
                <div class="mb-4 text-6xl">🛒</div>
                <p class="mb-6 text-lg text-slate-600">Your cart is empty.</p>
                <Link :href="route('cart.index')"
                      class="rounded-xl bg-orange-500 px-6 py-3 font-semibold text-white hover:bg-orange-600">
                    Back to Cart
                </Link>
            </div>

            <template v-else>

                <!-- Breadcrumb -->
                <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500">
                    <Link :href="route('cart.index')" class="hover:text-orange-600">Cart</Link>
                    <span>›</span>
                    <span :class="phase === 'form' ? 'font-semibold text-slate-900' : 'text-slate-400'">Details</span>
                    <span>›</span>
                    <span :class="phase !== 'form' ? 'font-semibold text-slate-900' : 'text-slate-400'">Payment</span>
                </nav>

                <!-- Server error -->
                <div v-if="serverError"
                     class="mb-6 flex items-start gap-3 rounded-xl bg-red-50 p-4 text-sm text-red-700 border border-red-200">
                    <span class="shrink-0 text-lg">⚠️</span>
                    <p>{{ serverError }}</p>
                </div>

                <div class="grid grid-cols-1 gap-8 lg:grid-cols-5">

                    <!-- ── Left: Form / Payment ───────────────────────────────── -->
                    <div class="lg:col-span-3">

                        <!-- ═══════════════════════════════ PHASE: FORM ═══════ -->
                        <form v-if="phase === 'form'" @submit.prevent="submitOrder" class="space-y-6">

                            <!-- Contact Information -->
                            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                <h2 class="mb-5 flex items-center gap-2 text-base font-bold text-slate-900">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-orange-500 text-xs font-bold text-white">1</span>
                                    Contact Information
                                </h2>

                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">
                                            Full Name *
                                        </label>
                                        <input v-model="form.contact.name" type="text"
                                               class="mt-1 w-full rounded-lg border px-3 py-2.5 text-sm outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                                               :class="fieldError('contact_name') ? 'border-red-400' : 'border-slate-200'"
                                               placeholder="Jane Smith" />
                                        <p v-if="fieldError('contact_name')" class="mt-1 text-xs text-red-500">{{ fieldError('contact_name') }}</p>
                                    </div>

                                    <div v-if="!user">
                                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">
                                            Email Address *
                                        </label>
                                        <input v-model="form.contact.email" type="email"
                                               class="mt-1 w-full rounded-lg border px-3 py-2.5 text-sm outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                                               :class="fieldError('contact_email') ? 'border-red-400' : 'border-slate-200'"
                                               placeholder="jane@example.com" />
                                        <p v-if="fieldError('contact_email')" class="mt-1 text-xs text-red-500">{{ fieldError('contact_email') }}</p>
                                    </div>

                                    <div :class="user ? 'sm:col-span-2' : ''">
                                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">
                                            Phone Number
                                        </label>
                                        <input v-model="form.contact.phone" type="tel"
                                               class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                                               placeholder="+852 9000 0000" />
                                    </div>
                                </div>

                                <!-- Guest prompt to sign in -->
                                <div v-if="!user" class="mt-4 rounded-lg bg-slate-50 px-4 py-3 text-xs text-slate-500">
                                    Have an account?
                                    <Link :href="route('login')" class="font-semibold text-orange-600 hover:underline">Sign in</Link>
                                    for faster checkout and order tracking.
                                </div>
                            </section>

                            <!-- Shipping Address -->
                            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                <h2 class="mb-5 flex items-center gap-2 text-base font-bold text-slate-900">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-orange-500 text-xs font-bold text-white">2</span>
                                    Shipping Address
                                </h2>

                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">
                                            Address Line 1 *
                                        </label>
                                        <input v-model="form.shipping.address_line_1" type="text"
                                               class="mt-1 w-full rounded-lg border px-3 py-2.5 text-sm outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                                               :class="fieldError('shipping_address_line_1') ? 'border-red-400' : 'border-slate-200'"
                                               placeholder="Flat 5A, Block 3, Green Valley Estate" />
                                        <p v-if="fieldError('shipping_address_line_1')" class="mt-1 text-xs text-red-500">{{ fieldError('shipping_address_line_1') }}</p>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">
                                            Address Line 2
                                        </label>
                                        <input v-model="form.shipping.address_line_2" type="text"
                                               class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                                               placeholder="Building name, floor, etc." />
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">
                                            District *
                                        </label>
                                        <select v-model="form.shipping.district"
                                                class="mt-1 w-full rounded-lg border px-3 py-2.5 text-sm outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                                                :class="fieldError('shipping_district') ? 'border-red-400' : 'border-slate-200'">
                                            <option value="" disabled>Select district</option>
                                            <option v-for="d in shippingDistricts" :key="d.district" :value="d.district">
                                                {{ d.district }} — ${{ fmt(d.rate) }}
                                            </option>
                                        </select>
                                        <p v-if="fieldError('shipping_district')" class="mt-1 text-xs text-red-500">{{ fieldError('shipping_district') }}</p>
                                        <p v-if="form.shipping.district && shippingCost > 0" class="mt-1 text-xs text-emerald-600">
                                            Shipping to this district: ${{ fmt(shippingCost) }}
                                        </p>
                                        <p v-if="form.shipping.district && shippingCost === 0" class="mt-1 text-xs text-emerald-600">
                                            Free shipping to this district!
                                        </p>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">
                                            City *
                                        </label>
                                        <input v-model="form.shipping.city" type="text"
                                               class="mt-1 w-full rounded-lg border px-3 py-2.5 text-sm outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                                               :class="fieldError('shipping_city') ? 'border-red-400' : 'border-slate-200'"
                                               placeholder="Hong Kong" />
                                        <p v-if="fieldError('shipping_city')" class="mt-1 text-xs text-red-500">{{ fieldError('shipping_city') }}</p>
                                    </div>
                                </div>

                                <!-- Billing address toggle -->
                                <div class="mt-5 flex items-center gap-2">
                                    <input id="billing-same" v-model="form.billing_same" type="checkbox"
                                           class="h-4 w-4 rounded border-slate-300 text-orange-500 focus:ring-orange-400" />
                                    <label for="billing-same" class="text-sm text-slate-700 select-none cursor-pointer">
                                        Billing address same as shipping
                                    </label>
                                </div>

                                <!-- Billing address form (shown when different) -->
                                <div v-if="!form.billing_same" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 border-t border-slate-100 pt-4">
                                    <h3 class="sm:col-span-2 text-sm font-semibold text-slate-700">Billing Address</h3>

                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Address Line 1 *</label>
                                        <input v-model="form.billing.address_line_1" type="text"
                                               class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100" />
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">District *</label>
                                        <select v-model="form.billing.district"
                                                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
                                            <option value="" disabled>Select district</option>
                                            <option v-for="d in shippingDistricts" :key="d.district" :value="d.district">{{ d.district }}</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">City *</label>
                                        <input v-model="form.billing.city" type="text"
                                               class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100" />
                                    </div>
                                </div>
                            </section>

                            <!-- Coupon Code -->
                            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                <h2 class="mb-4 flex items-center gap-2 text-base font-bold text-slate-900">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-orange-500 text-xs font-bold text-white">3</span>
                                    Coupon Code
                                </h2>

                                <div v-if="appliedCoupon"
                                     class="flex items-center justify-between rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3">
                                    <div class="flex items-center gap-2 text-sm font-medium text-emerald-700">
                                        <span class="text-base">🎉</span>
                                        <span>
                                            <strong>{{ appliedCoupon.code }}</strong> applied —
                                            {{ appliedCoupon.type === 'percent' ? `${appliedCoupon.value}% off` : `$${fmt(appliedCoupon.value)} off` }}
                                        </span>
                                    </div>
                                    <button @click="removeCoupon" type="button"
                                            class="text-sm text-emerald-600 hover:text-red-500 transition underline">
                                        Remove
                                    </button>
                                </div>

                                <div v-else class="flex gap-2">
                                    <input v-model="form.coupon_code" type="text"
                                           class="flex-1 rounded-lg border border-slate-200 px-3 py-2.5 text-sm uppercase tracking-widest outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                                           :class="couponError ? 'border-red-400' : ''"
                                           placeholder="WELCOME10"
                                           @keydown.enter.prevent="applyCoupon" />
                                    <button @click="applyCoupon" type="button" :disabled="couponLoading"
                                            class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700 disabled:opacity-50">
                                        <span v-if="couponLoading">...</span>
                                        <span v-else>Apply</span>
                                    </button>
                                </div>
                                <p v-if="couponError" class="mt-2 text-xs text-red-500">{{ couponError }}</p>
                            </section>

                            <!-- Notes -->
                            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                <label class="block text-sm font-semibold text-slate-700">
                                    Order Notes <span class="font-normal text-slate-400">(optional)</span>
                                </label>
                                <textarea v-model="form.notes" rows="3"
                                          class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                                          placeholder="Special instructions, gift note, etc." />
                            </section>

                            <!-- Submit -->
                            <button type="submit"
                                    class="w-full rounded-xl bg-orange-500 py-4 text-center font-bold text-white shadow transition hover:bg-orange-600 active:scale-[0.98]">
                                Place Order — ${{ fmt(total) }}
                            </button>
                        </form>

                        <!-- ════════════════════════════ PHASE: PAYMENT ════════ -->
                        <div v-else-if="phase === 'payment'" class="space-y-4">
                            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                <h2 class="mb-1 text-base font-bold text-slate-900">
                                    🔐 Complete Your Payment
                                </h2>
                                <p class="mb-5 text-sm text-slate-500">
                                    Order #{{ orderId }} created. Enter your payment details below.
                                </p>

                                <!-- Stripe Payment Element mount point -->
                                <div id="stripe-payment-element" class="mb-4" />

                                <p v-if="stripeError" class="mb-3 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600">
                                    {{ stripeError }}
                                </p>

                                <!-- Payment method placeholders -->
                                <div class="mb-4 flex items-center gap-2 text-xs text-slate-400">
                                    <span class="rounded bg-slate-100 px-2 py-1">Alipay — coming soon</span>
                                    <span class="rounded bg-slate-100 px-2 py-1">WeChat Pay — coming soon</span>
                                    <span class="rounded bg-slate-100 px-2 py-1">FPS / PayMe — coming soon</span>
                                </div>

                                <button @click="confirmPayment"
                                        class="w-full rounded-xl bg-orange-500 py-4 font-bold text-white shadow transition hover:bg-orange-600 active:scale-[0.98]">
                                    Pay ${{ fmt(total) }} Now
                                </button>

                                <p class="mt-3 text-center text-xs text-slate-400">
                                    🔒 Secured by Stripe · 256-bit SSL encryption
                                </p>
                            </div>
                        </div>

                        <!-- ════════════════════════════ PHASE: PROCESSING ═════ -->
                        <div v-else class="flex flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white p-12 shadow-sm">
                            <div class="mb-4 animate-spin text-4xl">🎲</div>
                            <p class="text-slate-600">Processing your order…</p>
                        </div>

                    </div>

                    <!-- ── Right: Order Summary sidebar ──────────────────────── -->
                    <div class="lg:col-span-2">
                        <div class="sticky top-24 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <h2 class="mb-4 text-base font-bold text-slate-900">
                                Order Summary
                            </h2>

                            <!-- Item list -->
                            <ul class="mb-4 space-y-3">
                                <li v-for="item in cart.items"
                                    :key="`sum-${item.product.id}-${item.variant?.id}`"
                                    class="flex items-center gap-3">
                                    <div class="relative shrink-0">
                                        <img v-if="item.product.image_url"
                                             :src="item.product.image_url"
                                             :alt="item.product.name"
                                             class="h-12 w-12 rounded-lg object-cover" />
                                        <div v-else class="flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100 text-xl">🎲</div>
                                        <span class="absolute -right-1.5 -top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-slate-600 text-[10px] font-bold text-white">
                                            {{ item.quantity }}
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-xs font-medium text-slate-800">{{ item.product.name }}</p>
                                        <p v-if="item.variant" class="text-xs text-slate-400">{{ item.variant.name }}</p>
                                    </div>
                                    <span class="shrink-0 text-xs font-semibold text-slate-800">
                                        ${{ fmt((item.variant?.price ?? item.product.price) * item.quantity) }}
                                    </span>
                                </li>
                            </ul>

                            <!-- Totals -->
                            <div class="space-y-2 border-t border-slate-100 pt-4 text-sm">
                                <div class="flex justify-between text-slate-600">
                                    <span>Subtotal</span>
                                    <span>${{ fmt(subtotal) }}</span>
                                </div>
                                <div class="flex justify-between text-slate-600">
                                    <span>Shipping</span>
                                    <span v-if="form.shipping.district">
                                        {{ shippingCost === 0 ? 'Free' : `$${fmt(shippingCost)}` }}
                                    </span>
                                    <span v-else class="text-slate-400">Select district</span>
                                </div>
                                <div v-if="appliedCoupon" class="flex justify-between text-emerald-600">
                                    <span>Discount ({{ appliedCoupon.code }})</span>
                                    <span>−${{ fmt(discount) }}</span>
                                </div>
                            </div>

                            <div class="mt-4 flex justify-between border-t border-slate-200 pt-4 font-bold text-slate-900">
                                <span>Total</span>
                                <span class="text-lg text-orange-600">${{ fmt(total) }}</span>
                            </div>
                        </div>
                    </div>

                </div>
            </template>
        </div>
    </StorefrontLayout>
</template>
