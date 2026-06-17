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

export interface ShippingDistrict {
    district: string;
    rate: number;
}

export interface AppliedCoupon {
    id: number;
    code: string;
    type: 'fixed' | 'percent';
    value: number;
    discount: number;
}

export interface OrderItem {
    id: number;
    quantity: number;
    unit_price: number;
    total_price: number;
    product_name: string;
    product_sku: string;
    snapshot: {
        id: number;
        name: string;
        sku: string | null;
        price: number;
        variant_name: string | null;
        image_url: string | null;
    };
}

export interface Order {
    id: number;
    status: 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled' | 'refunded';
    customer_name: string;
    customer_email: string;
    shipping_address: {
        name: string;
        phone: string | null;
        address_line_1: string;
        address_line_2?: string;
        district: string;
        city: string;
    };
    subtotal: number;
    shipping_cost: number;
    discount: number;
    total: number;
    coupon_code: string | null;
    notes: string | null;
    created_at: string;
    items: OrderItem[];
}

export interface CheckoutForm {
    contact: {
        name: string;
        email: string;
        phone: string;
    };
    shipping: {
        address_line_1: string;
        address_line_2: string;
        district: string;
        city: string;
    };
    billing_same: boolean;
    billing: {
        address_line_1: string;
        address_line_2: string;
        district: string;
        city: string;
    };
    coupon_code: string;
    notes: string;
}
