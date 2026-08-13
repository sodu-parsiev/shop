<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\Density;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Density>
 */
class DensityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gsm = fake()->unique()->numberBetween(80, 400);

        return [
            'name' => "{$gsm} gsm",
            'gsm' => $gsm,
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
