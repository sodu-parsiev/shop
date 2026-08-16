<?php

use App\Enums\ProductStatus;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Content\Faq;
use App\Models\Content\HomePageContent;

test('it returns a successful response', function () {
    $this->get('/')->assertStatus(200);
});

test('it shows products flagged to show on landing and active', function () {
    $visible = Product::factory()->create([
        'name' => 'Базовая футболка — белая',
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);

    $hiddenByFlag = Product::factory()->create([
        'name' => 'Скрытый товар',
        'show_on_landing' => false,
        'status' => ProductStatus::Active,
    ]);

    $hiddenByStatus = Product::factory()->create([
        'name' => 'Неактивный товар',
        'show_on_landing' => true,
        'status' => ProductStatus::Inactive,
    ]);

    $response = $this->get('/');

    $response->assertSee($visible->name);
    $response->assertDontSee($hiddenByFlag->name);
    $response->assertDontSee($hiddenByStatus->name);
});

test('it shows active categories and hides inactive ones', function () {
    $active = Category::factory()->create(['name' => 'Базовые футболки', 'is_active' => true]);
    $inactive = Category::factory()->create(['name' => 'Скрытая категория', 'is_active' => false]);

    $response = $this->get('/');

    $response->assertSee($active->name);
    $response->assertDontSee($inactive->name);
});

test('it shows active faqs and hides inactive ones', function () {
    $active = Faq::factory()->create(['question' => 'Какой минимальный заказ?', 'is_active' => true]);
    $inactive = Faq::factory()->create(['question' => 'Скрытый вопрос', 'is_active' => false]);

    $response = $this->get('/');

    $response->assertSee($active->question);
    $response->assertDontSee($inactive->question);
});

test('home page content resolves nested keys with a fallback', function () {
    $content = HomePageContent::create([
        'content' => ['hero' => ['headline_main' => 'База, на которой строятся']],
    ]);

    expect($content->get('hero.headline_main'))->toBe('База, на которой строятся');
    expect($content->get('hero.missing_key', 'default'))->toBe('default');
});
