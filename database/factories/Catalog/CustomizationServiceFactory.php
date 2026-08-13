<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\CustomizationService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomizationService>
 */
class CustomizationServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
