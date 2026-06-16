<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import { useCartStore } from '@/stores/cart';
import type { Product, ProductVariant } from '@/types';

const props = defineProps<{ product: Product }>();

const cart = useCartStore();

// ── Image gallery ─────────────────────────────────────────────────────────────
const activeImageIndex = ref(0);
const activeImage = computed(() => props.product.images?.[activeImageIndex.value] ?? null);

// ── Variant selection (specific products) ─────────────────────────────────────
const selectedVariant = ref<ProductVariant | null>(
    props.product.variants?.[0] ?? null,
);

const effectivePrice = computed(() => {
    if (props.product.product_type === 'specific' && selectedVariant.value?.price) {
        return selectedVariant.value.price;
    }
    return props.product.price;
});

const inStock = computed(() => {
    if (props.product.product_type === 'specific' && selectedVariant.value) {
        return selectedVariant.value.stock > 0;
    }
    return props.product.stock > 0;
});

function addToCart() {
    cart.add(props.product, selectedVariant.value);
    addedFeedback.value = true;
    setTimeout(() => (addedFeedback.value = false), 1500);
}
const addedFeedback = ref(false);

// ── Random Capsule machine ─────────────────────────────────────────────────────
const isRolling = ref(false);
const capsuleResult = ref<ProductVariant | null>(null);
const displaySlot = ref('???');
const rollCount = ref(0);

const variants = computed(() => props.product.variants ?? []);

async function pullCapsule() {
    if (isRolling.value || variants.value.length === 0) return;

    isRolling.value = true;
    capsuleResult.value = null;
    rollCount.value++;

    // Pick winner upfront
    const winner = variants.value[Math.floor(Math.random() * variants.value.length)];

    // Rapid cycling through names — start fast, slow down
    const allNames = variants.value.map((v) => v.name);
    let index = 0;
    let delay = 60;
    const startTime = Date.now();
    const duration = 2600;

    await new Promise<void>((resolve) => {
        function tick() {
            const elapsed = Date.now() - startTime;
            if (elapsed >= duration) {
                resolve();
                return;
            }
            displaySlot.value = allNames[index % allNames.length];
            index++;
            // Ease out: delay grows from 60ms → 280ms
            delay = 60 + Math.floor((elapsed / duration) * 220);
            setTimeout(tick, delay);
        }
        tick();
    });

    // Snap to winner
    displaySlot.value = winner.name;
    isRolling.value = false;
    capsuleResult.value = winner;
}

function addCapsuleToCart() {
    if (!capsuleResult.value) return;
    cart.add(props.product, capsuleResult.value);
    addedFeedback.value = true;
    setTimeout(() => (addedFeedback.value = false), 1500);
}
</script>

<template>
    <Head :title="product.meta_title ?? product.name" />
    <StorefrontLayout>
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

            <!-- Breadcrumb -->
            <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500">
                <Link :href="route('home')" class="hover:text-orange-600">Home</Link>
                <span>/</span>
                <Link :href="route('products.index')" class="hover:text-orange-600">Products</Link>
                <span v-if="product.category">/</span>
                <Link v-if="product.category"
                      :href="route('products.index', { category: product.category.slug })"
                      class="hover:text-orange-600">{{ product.category.name }}</Link>
                <span>/</span>
                <span class="truncate text-slate-800 font-medium">{{ product.name }}</span>
            </nav>

            <div class="grid grid-cols-1 gap-12 lg:grid-cols-2">

                <!-- Image Gallery -->
                <div class="flex flex-col gap-4">
                    <!-- Main image -->
                    <div class="relative overflow-hidden rounded-2xl bg-slate-100 aspect-square">
                        <img v-if="activeImage"
                             :src="activeImage.url"
                             :alt="product.name"
                             class="h-full w-full object-cover transition duration-300 hover:scale-105 cursor-zoom-in" />
                        <div v-else class="flex h-full w-full items-center justify-center text-8xl">
                            🎁
                        </div>
                        <!-- Random badge -->
                        <span v-if="product.product_type === 'random'"
                              class="absolute left-4 top-4 rounded-full bg-purple-600 px-3 py-1 text-sm font-bold text-white shadow-lg">
                            🎲 Mystery Capsule
                        </span>
                    </div>

                    <!-- Thumbnails -->
                    <div v-if="(product.images?.length ?? 0) > 1"
                         class="flex gap-2 overflow-x-auto pb-1">
                        <button v-for="(img, i) in product.images" :key="img.id"
                                @click="activeImageIndex = i"
                                :class="[
                                    'h-16 w-16 shrink-0 overflow-hidden rounded-xl border-2 transition',
                                    activeImageIndex === i ? 'border-orange-500' : 'border-transparent hover:border-slate-300'
                                ]">
                            <img :src="img.thumb_url" :alt="product.name" class="h-full w-full object-cover" />
                        </button>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="flex flex-col">
                    <!-- Title & Meta -->
                    <div class="mb-1 flex items-center gap-2 text-sm text-slate-500">
                        <span v-if="product.brand">{{ product.brand.name }}</span>
                        <span v-if="product.brand && product.category">·</span>
                        <span v-if="product.category">
                            <Link :href="route('products.index', { category: product.category.slug })"
                                  class="hover:text-orange-600">{{ product.category.name }}</Link>
                        </span>
                    </div>
                    <h1 class="text-3xl font-extrabold text-slate-900">{{ product.name }}</h1>
                    <p v-if="product.sku" class="mt-1 text-xs text-slate-400">SKU: {{ product.sku }}</p>

                    <!-- Price -->
                    <div class="mt-4 flex items-baseline gap-3">
                        <span class="text-4xl font-extrabold text-orange-600">
                            ${{ Number(effectivePrice).toFixed(2) }}
                        </span>
                        <span v-if="!inStock" class="rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-600">
                            Out of Stock
                        </span>
                    </div>

                    <!-- Description -->
                    <div v-if="product.description"
                         class="mt-5 prose prose-sm max-w-none text-slate-600"
                         v-html="product.description" />

                    <div class="mt-6 border-t border-slate-100 pt-6">

                        <!-- ── SPECIFIC PRODUCT UI ─────────────────────── -->
                        <template v-if="product.product_type === 'specific'">

                            <!-- Variant selection -->
                            <div v-if="(product.variants?.length ?? 0) > 0" class="mb-5">
                                <label class="mb-2 block text-sm font-semibold text-slate-700">Select Option</label>
                                <div class="flex flex-wrap gap-2">
                                    <button v-for="v in product.variants" :key="v.id"
                                            @click="selectedVariant = v"
                                            :disabled="v.stock === 0"
                                            :class="[
                                                'rounded-xl border-2 px-4 py-2 text-sm font-medium transition',
                                                selectedVariant?.id === v.id
                                                    ? 'border-orange-500 bg-orange-50 text-orange-700'
                                                    : v.stock === 0
                                                        ? 'border-slate-200 text-slate-300 cursor-not-allowed line-through'
                                                        : 'border-slate-200 text-slate-700 hover:border-orange-300',
                                            ]">
                                        {{ v.name }}
                                        <span v-if="v.price" class="ml-1 text-xs opacity-70">+${{ v.price.toFixed(2) }}</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Add to Cart -->
                            <button @click="addToCart" :disabled="!inStock"
                                    :class="[
                                        'flex w-full items-center justify-center gap-2 rounded-2xl py-4 text-lg font-bold transition',
                                        inStock
                                            ? addedFeedback
                                                ? 'bg-green-500 text-white scale-95'
                                                : 'bg-orange-500 text-white hover:bg-orange-600 active:scale-95'
                                            : 'cursor-not-allowed bg-slate-200 text-slate-400',
                                    ]">
                                <span v-if="addedFeedback">✓ Added to Cart!</span>
                                <span v-else-if="inStock">🛒 Add to Cart</span>
                                <span v-else>Out of Stock</span>
                            </button>
                        </template>

                        <!-- ── RANDOM CAPSULE UI ───────────────────────── -->
                        <template v-else>
                            <div class="rounded-2xl border-2 border-purple-200 bg-gradient-to-b from-purple-50 to-white p-6">
                                <div class="mb-5 text-center">
                                    <div class="text-5xl">🎰</div>
                                    <h3 class="mt-2 text-xl font-extrabold text-purple-800">Mystery Capsule Machine</h3>
                                    <p class="mt-1 text-sm text-purple-600">
                                        {{ variants.length > 0 ? `${variants.length} possible items inside!` : 'Pull to reveal your surprise!' }}
                                    </p>
                                </div>

                                <!-- Slot machine drum -->
                                <div class="relative mx-auto mb-5 h-20 w-full max-w-xs overflow-hidden rounded-2xl border-4 border-purple-400 bg-white shadow-inner">
                                    <!-- Shimmer overlay when rolling -->
                                    <div v-if="isRolling"
                                         class="pointer-events-none absolute inset-0 z-10 animate-pulse rounded-xl bg-purple-100/60" />
                                    <!-- Fade edges -->
                                    <div class="pointer-events-none absolute inset-x-0 top-0 z-10 h-6 bg-gradient-to-b from-white" />
                                    <div class="pointer-events-none absolute inset-x-0 bottom-0 z-10 h-6 bg-gradient-to-t from-white" />

                                    <!-- Current display -->
                                    <Transition name="slot" mode="out-in">
                                        <div :key="displaySlot"
                                             class="flex h-20 items-center justify-center px-4 text-center text-xl font-extrabold text-purple-800">
                                            {{ displaySlot }}
                                        </div>
                                    </Transition>
                                </div>

                                <!-- Result reveal -->
                                <Transition name="reveal">
                                    <div v-if="capsuleResult && !isRolling"
                                         class="mb-5 rounded-xl bg-purple-100 p-4 text-center">
                                        <div class="text-3xl">🎉</div>
                                        <p class="mt-1 text-sm text-purple-700">You got:</p>
                                        <p class="text-xl font-extrabold text-purple-900">{{ capsuleResult.name }}</p>
                                    </div>
                                </Transition>

                                <!-- Pull / Re-roll button -->
                                <button @click="pullCapsule" :disabled="isRolling || !inStock"
                                        :class="[
                                            'flex w-full items-center justify-center gap-2 rounded-2xl py-4 text-lg font-extrabold transition',
                                            isRolling
                                                ? 'animate-pulse cursor-wait bg-purple-400 text-white'
                                                : !inStock
                                                    ? 'cursor-not-allowed bg-slate-200 text-slate-400'
                                                    : 'bg-purple-600 text-white shadow-lg hover:bg-purple-700 active:scale-95',
                                        ]">
                                    <span v-if="isRolling">🎰 Rolling…</span>
                                    <span v-else-if="capsuleResult">🔄 Re-roll!</span>
                                    <span v-else-if="!inStock">Out of Stock</span>
                                    <span v-else>🎲 Pull!</span>
                                </button>

                                <!-- Add result to cart -->
                                <Transition name="reveal">
                                    <button v-if="capsuleResult && !isRolling && inStock"
                                            @click="addCapsuleToCart"
                                            :class="[
                                                'mt-3 flex w-full items-center justify-center gap-2 rounded-2xl py-3 text-sm font-bold transition',
                                                addedFeedback
                                                    ? 'bg-green-500 text-white scale-95'
                                                    : 'bg-orange-500 text-white hover:bg-orange-600 active:scale-95',
                                            ]">
                                        <span v-if="addedFeedback">✓ Added!</span>
                                        <span v-else>🛒 Add This Capsule to Cart</span>
                                    </button>
                                </Transition>
                            </div>
                        </template>

                    </div>

                    <!-- Stock indicator -->
                    <p v-if="product.stock > 0 && product.stock <= 10" class="mt-3 text-sm text-amber-600 font-medium">
                        ⚡ Only {{ product.stock }} left in stock!
                    </p>
                </div>

            </div>
        </div>
    </StorefrontLayout>
</template>

<style scoped>
/* Slot machine spin transition */
.slot-enter-active,
.slot-leave-active {
    transition: all 0.08s ease;
}
.slot-enter-from {
    transform: translateY(100%);
    opacity: 0;
}
.slot-leave-to {
    transform: translateY(-100%);
    opacity: 0;
}

/* Result reveal */
.reveal-enter-active {
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.reveal-leave-active {
    transition: all 0.2s ease;
}
.reveal-enter-from {
    transform: scale(0.8);
    opacity: 0;
}
.reveal-leave-to {
    transform: scale(0.95);
    opacity: 0;
}
</style>
