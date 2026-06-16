You are a senior Laravel architect. Follow "Claude Code Vibe": clean, thoughtful, production-grade, well-commented code.

Project: Gashapon Store

Follow strictly:
- /docs/04_database_schema.md
- /docs/01_project_scope.md
- /docs/03_architecture.md
- /docs/features/product_import.md

Task: Implement full core database layer for Day 2.

Please create / update:

1. All migration files (especially products, product_variants, shipping_rates, coupons)
2. All Models with full relationships, scopes, accessors (Product, ProductVariant, Category, Brand, Coupon, ShippingRate, Order, OrderItem, etc.)
3. Basic Factories
4. ImportProductsCommand that can read CSV and auto-download images

Key features to support:
- product_type: 'specific' or 'random'
- B2B wholesale_price
- Spatie Media Library on Product
- Random capsule support

Generate code step by step, starting with Product migration and model.


