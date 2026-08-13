<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\Color;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Color>
 */
class ColorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->safeColorName(),
            'hex_code' => fake()->hexColor(),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
