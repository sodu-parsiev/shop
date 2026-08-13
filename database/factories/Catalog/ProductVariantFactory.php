<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\Color;
use App\Models\Catalog\Density;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use App\Models\Catalog\Size;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
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
            'color_id' => Color::factory(),
            'size_id' => Size::factory(),
            'density_id' => Density::factory(),
        ];
    }
}
