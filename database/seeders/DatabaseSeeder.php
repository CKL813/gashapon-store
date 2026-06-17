<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ── Admin user ────────────────────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@gashapon.test'],
            ['name' => 'Admin', 'password' => bcrypt('password')],
        );
        $admin->assignRole('admin');

        // ── Test user ─────────────────────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('password')],
        );

        // ── Welcome coupon ────────────────────────────────────────────────────
        Coupon::firstOrCreate(
            ['code' => 'WELCOME10'],
            [
                'type'             => \App\Enums\CouponType::Fixed,
                'value'            => 10.00,
                'min_order_amount' => 50.00,
                'max_uses'         => null,
                'used_count'       => 0,
                'expires_at'       => null,
                'is_active'        => true,
                'is_welcome'       => true,
            ],
        );

        // ── Shipping rates ────────────────────────────────────────────────────
        $this->call(ShippingRateSeeder::class);

        // ── Products (brands + categories + 100 products) ─────────────────────
        $this->call(ProductSeeder::class);
    }
}
