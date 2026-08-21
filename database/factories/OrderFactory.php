<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'request_number' => null,
            'customer_name' => fake()->name(),
            'company' => fake()->optional()->company(),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'preferred_contact_method' => fake()->randomElement(['phone', 'email']),
            'message' => fake()->optional()->sentence(),
            'consent_accepted_at' => now(),
            'consent_ip' => fake()->ipv4(),
            'submission_token' => fake()->uuid(),
            'landing_url' => fake()->optional()->url(),
            'source_url' => fake()->optional()->url(),
            'referrer_url' => fake()->optional()->url(),
            'utm_source' => fake()->optional()->word(),
            'utm_medium' => fake()->optional()->word(),
            'utm_campaign' => fake()->optional()->word(),
            'utm_content' => fake()->optional()->word(),
            'utm_term' => fake()->optional()->word(),
            'status' => fake()->randomElement(OrderStatus::cases()),
            'internal_notes' => null,
            'assigned_to' => null,
        ];
    }
}
