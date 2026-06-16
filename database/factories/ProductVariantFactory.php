<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    private static array $colors = ['Red', 'Blue', 'Green', 'Black', 'White', 'Pink', 'Yellow'];
    private static array $sizes  = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

    public function definition(): array
    {
        $color = fake()->randomElement(self::$colors);
        $size  = fake()->randomElement(self::$sizes);

        return [
            'product_id'     => Product::factory(),
            'sku'            => strtoupper(Str::random(10)),
            'name'           => "{$color} / {$size}",
            'price'          => null, // inherits product price
            'wholesale_price'=> null,
            'stock'          => fake()->numberBetween(0, 50),
            'sort_order'     => 0,
            'is_active'      => true,
        ];
    }

    public function withPriceOverride(float $price): static
    {
        return $this->state([
            'price'           => $price,
            'wholesale_price' => round($price * 0.7, 2),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
