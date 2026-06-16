<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'parent_id'   => null,
            'name'        => ucwords($name),
            'slug'        => Str::slug($name),
            'description' => fake()->optional()->sentence(),
            'sort_order'  => fake()->numberBetween(0, 100),
            'is_active'   => true,
        ];
    }

    public function child(Category $parent): static
    {
        return $this->state(['parent_id' => $parent->id]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
