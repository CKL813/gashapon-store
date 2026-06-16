<?php

namespace Database\Factories;

use App\Enums\CouponType;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code'             => strtoupper(Str::random(8)),
            'type'             => CouponType::Fixed,
            'value'            => fake()->randomFloat(2, 5, 50),
            'min_order_amount' => null,
            'max_uses'         => null,
            'used_count'       => 0,
            'expires_at'       => null,
            'is_active'        => true,
            'is_welcome'       => false,
        ];
    }

    public function percent(): static
    {
        return $this->state([
            'type'  => CouponType::Percent,
            'value' => fake()->numberBetween(5, 30),
        ]);
    }

    public function welcome(): static
    {
        return $this->state([
            'code'       => 'WELCOME10',
            'type'       => CouponType::Fixed,
            'value'      => 10.00,
            'is_welcome' => true,
        ]);
    }

    public function expired(): static
    {
        return $this->state(['expires_at' => now()->subDays(1)]);
    }
}
