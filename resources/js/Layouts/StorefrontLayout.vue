<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useCartStore } from '@/stores/cart';
import type { PageProps } from '@/types';

const page = usePage<PageProps>();
const cart = useCartStore();

const user = computed(() => page.props.auth.user);
const navCategories = computed(() => page.props.navCategories ?? []);

const mobileOpen = ref(false);
const cartOpen = ref(false);
</script>

<template>
    <div class="min-h-screen bg-slate-50 font-sans">

        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-slate-200 bg-white shadow-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between gap-4">

                    <!-- Logo -->
                    <Link :href="route('home')" class="flex items-center gap-2 shrink-0">
                        <span class="text-2xl">🎲</span>
                        <span class="text-xl font-extrabold tracking-tight text-slate-900">
                            Gashapon<span class="text-orange-500">Store</span>
                        </span>
                    </Link>

                    <!-- Desktop Nav -->
                    <nav class="hidden items-center gap-1 md:flex">
                        <Link :href="route('products.index')"
                              class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-orange-50 hover:text-orange-600">
                            All Products
                        </Link>
                        <Link v-for="cat in navCategories" :key="cat.id"
                              :href="route('products.index', { category: cat.slug })"
                              class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-orange-50 hover:text-orange-600">
                            {{ cat.name }}
                        </Link>
                    </nav>

                    <!-- Right actions -->
                    <div class="flex items-center gap-3">
                        <!-- Cart -->
                        <button @click="cartOpen = !cartOpen"
                                class="relative rounded-lg p-2 text-slate-600 transition hover:bg-orange-50 hover:text-orange-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span v-if="cart.count > 0"
                                  class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-orange-500 text-xs font-bold text-white">
                                {{ cart.count }}
                            </span>
                        </button>

                        <!-- Auth links -->
                        <template v-if="user">
                            <Link :href="route('dashboard')"
                                  class="hidden rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-orange-50 hover:text-orange-600 sm:block">
                                My Account
                            </Link>
                        </template>
                        <template v-else>
                            <Link :href="route('login')"
                                  class="hidden rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:text-orange-600 sm:block">
                                Login
                            </Link>
                            <Link :href="route('register')"
                                  class="hidden rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-600 sm:block">
                                Sign Up
                            </Link>
                        </template>

                        <!-- Mobile menu toggle -->
                        <button @click="mobileOpen = !mobileOpen"
                                class="rounded-lg p-2 text-slate-600 md:hidden">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path v-if="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile menu -->
                <div v-if="mobileOpen" class="border-t border-slate-100 py-3 md:hidden">
                    <Link :href="route('products.index')" @click="mobileOpen = false"
                          class="block px-3 py-2 text-sm font-medium text-slate-700">All Products</Link>
                    <Link v-for="cat in navCategories" :key="cat.id"
                          :href="route('products.index', { category: cat.slug })"
                          @click="mobileOpen = false"
                          class="block px-3 py-2 text-sm font-medium text-slate-700">{{ cat.name }}</Link>
                </div>
            </div>
        </header>

        <!-- Cart Drawer -->
        <Transition name="slide-right">
            <div v-if="cartOpen" class="fixed inset-0 z-50 flex justify-end">
                <div class="absolute inset-0 bg-black/40" @click="cartOpen = false" />
                <div class="relative flex w-full max-w-sm flex-col bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b p-4">
                        <h2 class="text-lg font-semibold">Shopping Cart ({{ cart.count }})</h2>
                        <button @click="cartOpen = false" class="text-slate-500 hover:text-slate-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-4">
                        <div v-if="cart.items.length === 0" class="py-12 text-center text-slate-400">
                            <div class="mb-2 text-4xl">🛒</div>
                            <p>Your cart is empty</p>
                        </div>
                        <ul v-else class="space-y-4">
                            <li v-for="item in cart.items" :key="`${item.product.id}-${item.variant?.id}`"
                                class="flex items-center gap-3">
                                <img v-if="item.product.image_url" :src="item.product.image_url"
                                     :alt="item.product.name" class="h-16 w-16 rounded-lg object-cover" />
                                <div v-else class="flex h-16 w-16 items-center justify-center rounded-lg bg-slate-100 text-2xl">🎲</div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium">{{ item.product.name }}</p>
                                    <p v-if="item.variant" class="text-xs text-slate-500">{{ item.variant.name }}</p>
                                    <p class="text-sm font-semibold text-orange-600">
                                        ${{ ((item.variant?.price ?? item.product.price) * item.quantity).toFixed(2) }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button @click="cart.updateQty(item.product.id, item.variant?.id, item.quantity - 1)"
                                            class="flex h-6 w-6 items-center justify-center rounded border text-slate-600 hover:border-orange-400">−</button>
                                    <span class="w-6 text-center text-sm">{{ item.quantity }}</span>
                                    <button @click="cart.updateQty(item.product.id, item.variant?.id, item.quantity + 1)"
                                            class="flex h-6 w-6 items-center justify-center rounded border text-slate-600 hover:border-orange-400">+</button>
                                </div>
                                <button @click="cart.remove(item.product.id, item.variant?.id)"
                                        class="text-slate-400 hover:text-red-500">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div v-if="cart.items.length > 0" class="border-t p-4 space-y-3">
                        <div class="flex justify-between font-semibold">
                            <span>Total</span>
                            <span class="text-orange-600">${{ cart.total.toFixed(2) }}</span>
                        </div>
                        <Link :href="route('home')"
                              class="block w-full rounded-xl bg-orange-500 py-3 text-center font-semibold text-white transition hover:bg-orange-600">
                            Checkout
                        </Link>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Page Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="mt-20 border-t border-slate-200 bg-white">
            <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center gap-4 text-center">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">🎲</span>
                        <span class="text-lg font-extrabold text-slate-800">Gashapon<span class="text-orange-500">Store</span></span>
                    </div>
                    <p class="text-sm text-slate-500">Your premier destination for collectible capsule toys.</p>
                    <p class="text-xs text-slate-400">© {{ new Date().getFullYear() }} GashaponStore. All rights reserved.</p>
                </div>
            </div>
        </footer>

    </div>
</template>

<style scoped>
.slide-right-enter-active,
.slide-right-leave-active {
    transition: opacity 0.25s ease;
}
.slide-right-enter-from,
.slide-right-leave-to {
    opacity: 0;
}
</style>
