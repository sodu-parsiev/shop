<?php

namespace Database\Factories\Content;

use App\Models\Content\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

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
            'slug' => Str::slug($name),
            'title' => fake()->sentence(4),
            'body' => fake()->paragraphs(3, true),
            'page_type' => 'content',
            'is_published' => true,
            'sort_order' => fake()->numberBetween(0, 50),
            'meta_title' => fake()->optional()->sentence(6),
            'meta_description' => fake()->optional()->sentence(12),
            'canonical_url' => null,
            'og_image' => null,
        ];
    }
}
