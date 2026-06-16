<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import ProductCard from '@/Components/Storefront/ProductCard.vue';
import type { Category, Product } from '@/types';

defineProps<{
    featured: Product[];
    categories: Category[];
}>();
</script>

<template>
    <Head title="Home" />
    <StorefrontLayout>

        <!-- Hero -->
        <section class="relative overflow-hidden bg-gradient-to-br from-orange-500 via-orange-400 to-pink-500 py-24 text-white">
            <div class="pointer-events-none absolute inset-0 opacity-10">
                <div class="absolute left-10 top-5 text-8xl">🎲</div>
                <div class="absolute right-20 top-10 text-7xl">🎁</div>
                <div class="absolute bottom-10 left-40 text-6xl">⭐</div>
                <div class="absolute bottom-5 right-32 text-9xl">🎰</div>
            </div>
            <div class="relative mx-auto max-w-4xl px-4 text-center sm:px-6">
                <h1 class="text-4xl font-extrabold leading-tight tracking-tight sm:text-6xl">
                    Discover Your Next<br />
                    <span class="text-yellow-300">Surprise!</span>
                </h1>
                <p class="mx-auto mt-5 max-w-xl text-lg opacity-90">
                    Blind-box collectibles, mystery capsules, and unique figures — delivered to your door.
                </p>
                <div class="mt-8 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                    <Link :href="route('products.index')"
                          class="rounded-full bg-white px-8 py-3 text-lg font-bold text-orange-600 shadow-lg transition hover:scale-105 hover:shadow-xl">
                        Shop Now 🛍️
                    </Link>
                    <Link :href="route('products.index', { type: 'random' })"
                          class="rounded-full border-2 border-white px-8 py-3 text-lg font-bold text-white transition hover:bg-white hover:text-orange-600">
                        Try a Capsule 🎲
                    </Link>
                </div>
            </div>
        </section>

        <!-- Categories -->
        <section v-if="categories.length > 0" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-center justify-between">
                <h2 class="text-2xl font-extrabold text-slate-900">Shop by Category</h2>
                <Link :href="route('products.index')" class="text-sm font-medium text-orange-600 hover:underline">
                    View all →
                </Link>
            </div>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                <Link v-for="cat in categories" :key="cat.id"
                      :href="route('products.index', { category: cat.slug })"
                      class="group flex flex-col items-center rounded-2xl bg-white p-5 text-center shadow-md transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="mb-3 text-4xl">🎁</div>
                    <span class="text-sm font-semibold text-slate-800 group-hover:text-orange-600">{{ cat.name }}</span>
                    <span v-if="cat.products_count !== undefined"
                          class="mt-1 text-xs text-slate-400">{{ cat.products_count }} items</span>
                </Link>
            </div>
        </section>

        <!-- Featured Products -->
        <section v-if="featured.length > 0" class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-center justify-between">
                <h2 class="text-2xl font-extrabold text-slate-900">Featured Collectibles</h2>
                <Link :href="route('products.index')" class="text-sm font-medium text-orange-600 hover:underline">
                    View all →
                </Link>
            </div>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                <ProductCard v-for="product in featured" :key="product.id" :product="product" />
            </div>
        </section>

        <!-- Empty state -->
        <section v-if="featured.length === 0 && categories.length === 0"
                 class="mx-auto max-w-2xl px-4 py-24 text-center">
            <div class="mb-4 text-6xl">🎲</div>
            <h2 class="text-2xl font-bold text-slate-800">Coming Soon!</h2>
            <p class="mt-2 text-slate-500">Products are being stocked. Check back shortly!</p>
        </section>

    </StorefrontLayout>
</template>
