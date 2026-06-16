<?php

namespace Database\Factories;

use App\Enums\ProductType;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name  = fake()->unique()->words(fake()->numberBetween(2, 4), true);
        $price = fake()->randomFloat(2, 5, 500);

        return [
            'brand_id'        => Brand::factory(),
            'category_id'     => Category::factory(),
            'sku'             => strtoupper(Str::random(8)),
            'name'            => ucwords($name),
            'slug'            => Str::slug($name),
            'description'     => fake()->paragraph(),
            'product_type'    => ProductType::Specific,
            'price'           => $price,
            'wholesale_price' => round($price * 0.7, 2),
            'stock'           => fake()->numberBetween(0, 200),
            'is_active'       => true,
            'is_featured'     => false,
            'meta_title'      => null,
            'meta_description'=> null,
        ];
    }

    public function random(): static
    {
        return $this->state(['product_type' => ProductType::Random]);
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function outOfStock(): static
    {
        return $this->state(['stock' => 0]);
    }
}
