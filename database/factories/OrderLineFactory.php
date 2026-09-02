<?php

namespace Database\Factories;

use App\Models\Catalog\Product;
use App\Models\Order;
use App\Models\OrderLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderLine>
 */
class OrderLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => fake()->words(3, true),
            'category_name' => fake()->optional()->word(),
            'availability_label' => fake()->randomElement(['На складе', 'Под заказ']),
            'quantity' => 5000,
            'product_moq' => 5000,
            'unit_price' => fake()->optional()->randomFloat(2, 1, 15),
            'currency' => 'USD',
            'price_quantity_tier' => 5000,
            'price_note' => 'Чистый текстиль, без нанесения',
            'preferred_density' => fake()->optional()->randomElement(['180 gsm', '200 gsm', '240 gsm']),
            'preferred_size' => fake()->optional()->randomElement(['XS–XL', 'S–2XL']),
            'preferred_color' => fake()->optional()->randomElement(['Белый', 'Чёрный', 'Цвет по ТЗ']),
        ];
    }
}
