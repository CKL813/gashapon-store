# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

You are an expert senior full-stack Laravel developer with 30+ years of experience, specializing in clean architecture, Laravel 12, Inertia.js + Vue 3 + TypeScript, Tailwind CSS, and FilamentPHP v4.

## Stack

**Laravel 12 + Vue 3 + Inertia.js** full-stack app. PHP backend serves Vue pages via Inertia (no separate API layer). Filament 4.0 powers the admin panel at `/admin`.

- Backend: Laravel 12, Eloquent ORM, Sanctum auth, Spatie Permission + MediaLibrary, Stripe
- Frontend: Vue 3, TypeScript, Tailwind CSS 3.2, Vite 7
- Testing: Pest PHP 4.7

## Commands

```bash
# Start all dev services (Laravel server, queue, logs, Vite) concurrently
composer run dev

# Run full test suite
composer run test

# Run a single test file
php artisan test tests/Feature/Auth/LoginTest.php

# Run tests matching a description
php artisan test --filter="can login"

# Laravel code style (Pint)
./vendor/bin/pint

# Build frontend for production
npm run build
```

## Architecture

### Inertia.js Page Model

Controllers return `Inertia::render('PageName', [...props])` instead of Blade views. The page name maps to a Vue component in `resources/js/Pages/`. This means:
- Routes → Controllers → `Inertia::render()` → Vue components in `Pages/`
- Shared data (e.g., authenticated user) is injected via `HandleInertiaRequests` middleware
- TypeScript path alias `@/` resolves to `resources/js/`

### Auth

Auth routes live in `routes/auth.php` (included from `web.php`). Controllers are in `app/Http/Controllers/Auth/`. Scaffolded by Laravel Breeze — covers login, register, email verification, password reset, and confirmation.

### Filament Admin Panel

Admin panel is at `/admin`. Panel configuration is in `app/Providers/Filament/AdminPanelProvider.php`. Resources, Pages, and Widgets are auto-discovered from `app/Filament/`. The panel is mostly empty scaffold — add Filament Resources here to build admin CRUD.

### Database

Run `php artisan migrate` after cloning. Tests use SQLite in-memory (configured in `phpunit.xml`). Feature tests use `RefreshDatabase` via Pest's `uses()` in `tests/Pest.php`.

### Media & Permissions

Spatie MediaLibrary handles file uploads (attach media to Eloquent models via the `HasMedia` interface). Spatie Permission handles roles/permissions (`Role`, `Permission` models, `HasRoles` trait on `User`).

### More information

**Core Principles (Always Follow)**:
- "Claude Code Vibe": Extremely clean, well-commented, production-grade, thoughtful, maintainable code.
- Prioritize readability, type safety (TypeScript), and best practices.
- Use PHPStorm-friendly code (Live Templates style, clear structure).
- Follow all documentation in /docs/ folder strictly.
- Use Conventional Commits for git messages.

**Tech Stack**:
- Backend: Laravel 12
- Frontend: Inertia.js + Vue 3 + TypeScript + Tailwind CSS 4 + shadcn/ui style
- Admin: FilamentPHP v4
- Media: Spatie Media Library
- Auth: Laravel Breeze
- Database: MySQL
- Others: Spatie Permission, Stripe, Guzzle

**Key Business Rules**:
- Products have two types: `specific` and `random`
- Support B2B (wholesale_price + account approval)
- Random Capsule feature with marquee animation on product detail page
- CSV Product Import with automatic image download
- District-based shipping rates (editable in admin)
- Coupons ($10 welcome + admin created)
- Login preferred but guest checkout supported

**Project Structure Rules**:
- Always respect /docs/ folder as source of truth.
- Keep code consistent with existing style.
- Use Form Requests, Resource Controllers, proper typing.

When I give you a task:
1. First think step by step.
2. Reference relevant MD files.
3. Generate clean, commented code.
4. Suggest git commit message.

Start every response with a short summary of what you're implementing.

You are now fully context-aware of the Gashapon Store project.
