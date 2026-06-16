export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    is_b2b?: boolean;
    is_approved?: boolean;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User | null;
    };
    navCategories: NavCategory[];
};

export interface NavCategory {
    id: number;
    name: string;
    slug: string;
}

export interface Category {
    id: number;
    name: string;
    slug: string;
    description?: string;
    products_count?: number;
}

export interface Brand {
    id: number;
    name: string;
    slug: string;
}

export interface ProductImage {
    id: number;
    url: string;
    thumb_url: string;
    original_url: string;
}

export interface ProductVariant {
    id: number;
    name: string;
    sku: string | null;
    price: number | null;
    stock: number;
    is_active: boolean;
}

export interface Product {
    id: number;
    name: string;
    slug: string;
    sku: string | null;
    description: string | null;
    product_type: 'specific' | 'random';
    price: number;
    wholesale_price: number | null;
    stock: number;
    is_active: boolean;
    is_featured: boolean;
    meta_title: string | null;
    meta_description: string | null;
    category?: Category | null;
    brand?: Brand | null;
    variants?: ProductVariant[];
    images?: ProductImage[];
    image_url?: string | null;
}

export interface CartItem {
    product: Pick<Product, 'id' | 'name' | 'slug' | 'price' | 'image_url' | 'product_type'>;
    variant: ProductVariant | null;
    quantity: number;
}

export interface PaginatedData<T> {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    from: number | null;
    last_page: number;
    per_page: number;
    to: number | null;
    total: number;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}
