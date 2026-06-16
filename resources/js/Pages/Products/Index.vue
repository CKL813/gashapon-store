<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import ProductCard from '@/Components/Storefront/ProductCard.vue';
import type { Category, PaginatedData, Product } from '@/types';

const props = defineProps<{
    products: PaginatedData<Product>;
    categories: Category[];
    filters: {
        category?: string;
        search?: string;
        type?: string;
        min_price?: string;
        max_price?: string;
        sort?: string;
    };
}>();

const search = ref(props.filters.search ?? '');
const selectedCategory = ref(props.filters.category ?? '');
const selectedType = ref(props.filters.type ?? '');
const sort = ref(props.filters.sort ?? '');

let searchTimer: ReturnType<typeof setTimeout>;

function applyFilters() {
    router.get(route('products.index'), {
        search: search.value || undefined,
        category: selectedCategory.value || undefined,
        type: selectedType.value || undefined,
        sort: sort.value || undefined,
    }, { preserveScroll: true, replace: true });
}

watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 400);
});

watch([selectedCategory, selectedType, sort], applyFilters);

function clearFilters() {
    search.value = '';
    selectedCategory.value = '';
    selectedType.value = '';
    sort.value = '';
    router.get(route('products.index'));
}

const hasFilters = () =>
    !!search.value || !!selectedCategory.value || !!selectedType.value || !!sort.value;
</script>

<template>
    <Head title="Products" />
    <StorefrontLayout>
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

            <!-- Page heading -->
            <div class="mb-8">
                <h1 class="text-3xl font-extrabold text-slate-900">All Products</h1>
                <p class="mt-1 text-slate-500">{{ products.meta.total }} collectibles available</p>
            </div>

            <div class="flex flex-col gap-8 lg:flex-row">

                <!-- Filters Sidebar -->
                <aside class="w-full shrink-0 lg:w-56">
                    <div class="sticky top-24 rounded-2xl bg-white p-5 shadow-md">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="font-semibold text-slate-800">Filters</h2>
                            <button v-if="hasFilters()" @click="clearFilters"
                                    class="text-xs text-orange-600 hover:underline">Clear all</button>
                        </div>

                        <!-- Search -->
                        <div class="mb-5">
                            <label class="mb-1 block text-xs font-medium text-slate-500 uppercase tracking-wide">Search</label>
                            <input v-model="search" type="search" placeholder="Search products…"
                                   class="w-full rounded-lg border-slate-200 text-sm focus:border-orange-400 focus:ring-orange-400" />
                        </div>

                        <!-- Category -->
                        <div class="mb-5">
                            <label class="mb-2 block text-xs font-medium text-slate-500 uppercase tracking-wide">Category</label>
                            <div class="space-y-1">
                                <button @click="selectedCategory = ''"
                                        :class="selectedCategory === '' ? 'bg-orange-100 text-orange-700 font-semibold' : 'text-slate-700 hover:bg-slate-50'"
                                        class="block w-full rounded-lg px-3 py-1.5 text-left text-sm transition">
                                    All
                                </button>
                                <button v-for="cat in categories" :key="cat.id"
                                        @click="selectedCategory = cat.slug"
                                        :class="selectedCategory === cat.slug ? 'bg-orange-100 text-orange-700 font-semibold' : 'text-slate-700 hover:bg-slate-50'"
                                        class="block w-full rounded-lg px-3 py-1.5 text-left text-sm transition">
                                    {{ cat.name }}
                                </button>
                            </div>
                        </div>

                        <!-- Type -->
                        <div class="mb-5">
                            <label class="mb-2 block text-xs font-medium text-slate-500 uppercase tracking-wide">Type</label>
                            <div class="space-y-1">
                                <button @click="selectedType = ''"
                                        :class="selectedType === '' ? 'bg-orange-100 text-orange-700 font-semibold' : 'text-slate-700 hover:bg-slate-50'"
                                        class="block w-full rounded-lg px-3 py-1.5 text-left text-sm transition">
                                    All Types
                                </button>
                                <button @click="selectedType = 'specific'"
                                        :class="selectedType === 'specific' ? 'bg-orange-100 text-orange-700 font-semibold' : 'text-slate-700 hover:bg-slate-50'"
                                        class="block w-full rounded-lg px-3 py-1.5 text-left text-sm transition">
                                    🧸 Specific Item
                                </button>
                                <button @click="selectedType = 'random'"
                                        :class="selectedType === 'random' ? 'bg-purple-100 text-purple-700 font-semibold' : 'text-slate-700 hover:bg-slate-50'"
                                        class="block w-full rounded-lg px-3 py-1.5 text-left text-sm transition">
                                    🎲 Random Capsule
                                </button>
                            </div>
                        </div>

                        <!-- Sort -->
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-500 uppercase tracking-wide">Sort By</label>
                            <select v-model="sort"
                                    class="w-full rounded-lg border-slate-200 text-sm focus:border-orange-400 focus:ring-orange-400">
                                <option value="">Newest</option>
                                <option value="price_asc">Price: Low to High</option>
                                <option value="price_desc">Price: High to Low</option>
                            </select>
                        </div>
                    </div>
                </aside>

                <!-- Product Grid -->
                <div class="flex-1">
                    <div v-if="products.data.length > 0" class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-4">
                        <ProductCard v-for="product in products.data" :key="product.id" :product="product" />
                    </div>

                    <!-- Empty state -->
                    <div v-else class="flex flex-col items-center justify-center rounded-2xl bg-white py-24 text-center shadow-md">
                        <div class="mb-3 text-5xl">🔍</div>
                        <h3 class="text-lg font-semibold text-slate-800">No products found</h3>
                        <p class="mt-1 text-sm text-slate-500">Try adjusting your filters</p>
                        <button @click="clearFilters" class="mt-4 rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-600">
                            Clear Filters
                        </button>
                    </div>

                    <!-- Pagination -->
                    <div v-if="products.meta.last_page > 1" class="mt-10 flex items-center justify-center gap-1">
                        <template v-for="link in products.links" :key="link.label">
                            <Link v-if="link.url"
                                  :href="link.url"
                                  v-html="link.label"
                                  :class="[
                                      'inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-3 text-sm transition',
                                      link.active
                                          ? 'bg-orange-500 font-semibold text-white'
                                          : 'bg-white text-slate-700 shadow-sm hover:bg-orange-50',
                                  ]" />
                            <span v-else
                                  v-html="link.label"
                                  class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-3 text-sm text-slate-400" />
                        </template>
                    </div>
                </div>

            </div>
        </div>
    </StorefrontLayout>
</template>
