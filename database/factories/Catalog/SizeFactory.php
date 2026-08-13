<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\Size;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Size>
 */
class SizeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
