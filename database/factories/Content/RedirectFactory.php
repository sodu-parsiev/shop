<?php

namespace Database\Factories\Content;

use App\Models\Content\Redirect;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Redirect>
 */
class RedirectFactory extends Factory
{
    protected $model = Redirect::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source_path' => '/old-'.fake()->unique()->slug(),
            'target_url' => '/',
            'status_code' => 301,
            'is_active' => true,
            'hits' => 0,
            'last_used_at' => null,
        ];
    }
}
