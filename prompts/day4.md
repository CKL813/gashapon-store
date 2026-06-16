You are a senior frontend developer specializing in Vue 3 + TypeScript + Tailwind.

Project: Gashapon Store

Follow strictly:
- /docs/07_ux_design_system.md (clean, easy to read, playful but professional Gashapon vibe)
- /docs/features/storefront/homepage.md
- /docs/features/storefront/product_listing.md
- /docs/features/storefront/product_detail.md (especially the Random Capsule marquee animation)

Tech: Inertia.js + Vue 3 + TypeScript + Tailwind CSS 4

Task: Implement core storefront pages.

Please create / update:

1. Layout / Navigation (header with logo, cart icon, search, category menu)
2. Homepage (Hero banner, Featured products, Categories grid)
3. Product Listing Page (`/products`)
    - Grid layout
    - Filters (category, price, product_type, search)
    - Pagination
4. Product Detail Page (`/products/{slug}`) — Very Important
    - Large image gallery with zoom
    - Product info, price (respect wholesale if B2B user)
    - **Random Capsule button** with marquee-style animation when clicked
    - Variant selection (if specific product)
    - Add to Cart
    - Description + meta

For Random Capsule:
- Nice marquee / spinning animation effect (CSS + Vue Transition)
- Show random item from the series after animation
- Allow "Re-roll" button
- Fun, engaging toy-collector feel

Use Pinia for cart state if needed.
Keep everything Inertia-friendly and TypeScript strict.

Generate step by step, starting with Layout + Homepage, then Product Listing, then Product Detail with animation.
