<?php

namespace Database\Factories\Catalog;

use App\Enums\AvailabilityStatus;
use App\Enums\ProductStatus;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'h1' => $name,
            'category_id' => Category::factory(),
            'sku' => fake()->unique()->bothify('SKU-#####'),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'composition' => fake()->sentence(),
            'fit' => fake()->randomElement(['Regular Fit', 'Slim Fit', 'Oversized']),
            'size_table' => [
                ['size' => 'S', 'chest' => '50', 'length' => '68', 'sleeve' => '20'],
                ['size' => 'M', 'chest' => '52', 'length' => '70', 'sleeve' => '21'],
            ],
            'moq' => fake()->numberBetween(1, 50),
            'stock_conditions' => fake()->optional()->sentence(),
            'availability_status' => fake()->randomElement(AvailabilityStatus::cases()),
            'stock_quantity' => fake()->optional(0.5)->numberBetween(0, 500),
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
