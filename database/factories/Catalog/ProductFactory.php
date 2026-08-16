<?php

namespace Database\Factories\Catalog;

use App\Enums\ProductStatus;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => $name,
            'category_id' => Category::factory(),
            'slug' => Str::slug($name),
            'sku' => fake()->unique()->bothify('SKU-#####'),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'composition' => fake()->sentence(),
            'fit' => fake()->randomElement(['Regular Fit', 'Slim Fit', 'Oversized']),
            'moq' => fake()->numberBetween(1, 50),
            'stock_conditions' => fake()->optional()->sentence(),
            'status' => ProductStatus::Active,
            'featured' => fake()->boolean(20),
            'show_on_landing' => fake()->boolean(50),
            'sort_order' => fake()->numberBetween(0, 100),
            'meta_title' => fake()->optional()->sentence(6),
            'meta_description' => fake()->optional()->sentence(12),
            'canonical_url' => fake()->optional()->url(),
            'og_image' => fake()->optional()->imageUrl(),
        ];
    }
}
