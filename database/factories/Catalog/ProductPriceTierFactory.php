<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductPriceTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductPriceTier>
 */
class ProductPriceTierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'quantity' => fake()->randomElement(ProductPriceTier::publicQuantities()),
            'unit_price' => fake()->randomFloat(2, 1, 15),
            'currency' => ProductPriceTier::DEFAULT_CURRENCY,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
