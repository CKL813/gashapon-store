<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Product } from '@/types';

defineProps<{ product: Product }>();
</script>

<template>
    <Link :href="route('products.show', product.slug)"
          class="group relative flex flex-col overflow-hidden rounded-2xl bg-white shadow-md transition hover:-translate-y-1 hover:shadow-xl">

        <!-- Badge -->
        <span v-if="product.product_type === 'random'"
              class="absolute left-3 top-3 z-10 rounded-full bg-purple-600 px-2.5 py-0.5 text-xs font-bold text-white shadow">
            🎲 Random
        </span>
        <span v-if="product.stock === 0"
              class="absolute right-3 top-3 z-10 rounded-full bg-red-500 px-2.5 py-0.5 text-xs font-bold text-white shadow">
            Sold Out
        </span>

        <!-- Image -->
        <div class="aspect-square overflow-hidden bg-slate-100">
            <img v-if="product.image_url"
                 :src="product.image_url"
                 :alt="product.name"
                 class="h-full w-full object-cover transition duration-300 group-hover:scale-105" />
            <div v-else class="flex h-full w-full items-center justify-center text-5xl text-slate-300">
                🎁
            </div>
        </div>

        <!-- Info -->
        <div class="flex flex-1 flex-col gap-1 p-4">
            <p v-if="product.category" class="text-xs font-medium uppercase tracking-wide text-slate-400">
                {{ product.category }}
            </p>
            <h3 class="line-clamp-2 text-sm font-semibold text-slate-800 group-hover:text-orange-600">
                {{ product.name }}
            </h3>
            <div class="mt-auto pt-2">
                <span class="text-lg font-bold text-orange-600">${{ Number(product.price).toFixed(2) }}</span>
            </div>
        </div>
    </Link>
</template>
