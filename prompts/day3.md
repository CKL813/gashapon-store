You are a senior Laravel + FilamentPHP expert.

Follow strictly:
- /docs/03_architecture.md
- /docs/04_database_schema.md
- /docs/features/admin_panel/product_management.md
- /docs/features/admin_panel/order_management.md
- /docs/features/admin_panel/coupon_management.md
- /docs/features/admin_panel/shipping_rates.md
- /docs/07_ux_design_system.md (clean & easy to read)

Task: Implement full Filament Admin Panel for Day 3.

Please create / improve:

1. CategoryResource, BrandResource (simple)
2. ProductResource (very important):
    - Form with repeater for variants
    - Spatie Media Library integration (multiple images)
    - Product Type (specific/random) toggle
    - Price + Wholesale Price fields
    - Import CSV Action button
3. ShippingRateResource (table editable by district)
4. CouponResource (with welcome coupon logic)
5. UserResource (B2B approval toggle, company fields, role assignment)
6. OrderResource (view only for now, with status management)
7. Update Dashboard with basic stats widgets

Use best practices for Filament v4 (Actions, Filters, Repeater, etc.).
Focus on usability for toy store owner.

Generate step by step, starting with ProductResource.
