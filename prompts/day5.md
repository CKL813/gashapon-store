You are a senior Laravel + Vue developer.

Follow strictly:
- /docs/features/storefront/cart.md
- /docs/features/storefront/checkout.md
- /docs/features/payments_and_stripe.md
- /docs/04_database_schema.md
- /docs/07_ux_design_system.md

Tech: Laravel 12 + Inertia.js + Vue 3 + TypeScript + Pinia + Tailwind

Task: Implement full Cart + Checkout + Payment system for Day 5.

Please implement:

1. Pinia Cart Store (`stores/cart.ts`)
    - Add to cart, remove, update quantity, clear cart
    - Calculate subtotal, shipping, total
    - Persist cart in localStorage

2. Cart Page (`/cart`) - Vue component with Inertia

3. Checkout Page (`/checkout`)
    - Multi-step or single page (your choice, justify)
    - Guest / Logged-in support
    - Address form
    - Coupon input + application
    - Shipping district selector with dynamic rate
    - Payment method selector (Stripe primary, Alipay, WeChat, FPS/PayMe placeholders)

4. Backend:
    - CartController / CheckoutController
    - Order creation logic (with product_snapshot)
    - Stock deduction (with proper locking if possible)
    - Stripe PaymentIntent creation

5. Payment flow:
    - Stripe Elements or Payment Link integration
    - Webhook endpoint placeholder (`/stripe/webhook`)

Focus on smooth UX, error handling, and TypeScript safety.
Start with Pinia Cart Store → Cart Page → Checkout flow.

Generate step by step.
