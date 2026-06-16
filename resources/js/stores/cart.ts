import { defineStore } from 'pinia';
import { computed, ref, watch } from 'vue';
import type { CartItem, Product, ProductVariant } from '@/types';

const STORAGE_KEY = 'gashapon_cart';

export const useCartStore = defineStore('cart', () => {
    const items = ref<CartItem[]>(loadFromStorage());

    const count = computed(() =>
        items.value.reduce((sum, item) => sum + item.quantity, 0),
    );

    const total = computed(() =>
        items.value.reduce((sum, item) => {
            const price = item.variant?.price ?? item.product.price;
            return sum + price * item.quantity;
        }, 0),
    );

    function add(product: Product, variant: ProductVariant | null = null, quantity = 1) {
        const existing = items.value.find(
            (i) => i.product.id === product.id && i.variant?.id === variant?.id,
        );
        if (existing) {
            existing.quantity += quantity;
        } else {
            items.value.push({
                product: {
                    id: product.id,
                    name: product.name,
                    slug: product.slug,
                    price: product.price,
                    image_url: product.image_url ?? null,
                    product_type: product.product_type,
                },
                variant,
                quantity,
            });
        }
    }

    function remove(productId: number, variantId?: number) {
        items.value = items.value.filter(
            (i) => !(i.product.id === productId && i.variant?.id === variantId),
        );
    }

    function updateQty(productId: number, variantId: number | undefined, quantity: number) {
        const item = items.value.find(
            (i) => i.product.id === productId && i.variant?.id === variantId,
        );
        if (item) {
            item.quantity = Math.max(1, quantity);
        }
    }

    function clear() {
        items.value = [];
    }

    watch(items, (val) => {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(val));
    }, { deep: true });

    return { items, count, total, add, remove, updateQty, clear };
});

function loadFromStorage(): CartItem[] {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        return raw ? JSON.parse(raw) : [];
    } catch {
        return [];
    }
}
