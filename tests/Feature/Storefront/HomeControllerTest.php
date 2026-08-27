<?php

use App\Enums\ProductStatus;
use App\Models\Catalog\Category;
use App\Models\Catalog\Color;
use App\Models\Catalog\Density;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductPriceTier;
use App\Models\Catalog\Size;
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
    Product::factory()->create([
        'category_id' => $active->id,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);
    Product::factory()->create([
        'category_id' => $inactive->id,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);

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

test('it renders mock storefront assets instead of placeholders', function () {
    Product::factory()->create([
        'name' => 'Базовая футболка — белая',
        'cover_image' => '/brand/catalog-white-v2.jpg',
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);

    $response = $this->get('/');

    $response->assertSee('/brand/logo-dark.png', false);
    $response->assertSee('/brand/model-motion.jpg', false);
    $response->assertSee('/brand/mark.png', false);
    $response->assertSee('/brand/catalog-white-v2.jpg', false);
    $response->assertSee('id="contacts"', false);
    $response->assertDontSee('placehold.co', false);
});

test('it renders admin editable seo metadata and structured data', function () {
    HomePageContent::query()->updateOrCreate(['id' => 1], [
        'content' => [
            'seo' => [
                'title' => 'Custom SEO title',
                'description' => 'Custom SEO description',
                'keywords' => 'custom, keywords',
                'canonical_url' => '/custom-canonical',
                'og_title' => 'Custom OG title',
                'og_description' => 'Custom OG description',
                'og_image' => '/brand/custom-og.jpg',
                'icon' => '/brand/custom-icon.png',
                'organization_name' => 'Custom Organization',
                'organization_description' => 'Custom organization description',
            ],
            'cta_section' => [
                'email' => 'sales@example.test',
            ],
        ],
    ]);
    Faq::factory()->create([
        'question' => 'Как оформить заказ?',
        'answer' => 'Добавьте товар и оставьте контакты.',
        'is_active' => true,
    ]);

    $response = $this->get('/');

    $response->assertSee('<title>Custom SEO title</title>', false);
    $response->assertSee('<meta name="description" content="Custom SEO description">', false);
    $response->assertSee('<meta name="keywords" content="custom, keywords">', false);
    $response->assertSee('<link rel="canonical" href="'.asset('custom-canonical').'">', false);
    $response->assertSee('<meta property="og:title" content="Custom OG title">', false);
    $response->assertSee('<meta name="twitter:image" content="'.asset('brand/custom-og.jpg').'">', false);
    $response->assertSee('<script type="application/ld+json">', false);
    $response->assertSee('Custom Organization', false);
    $response->assertSee('LocalBusiness', false);
    $response->assertSee('FAQPage', false);
    $response->assertSee('Как оформить заказ?', false);
});

test('it renders order builder hooks with real catalog filters and preferences', function () {
    $product = Product::factory()->create([
        'name' => 'Базовая футболка — белая',
        'moq' => 10,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);
    ProductPriceTier::factory()->create([
        'product_id' => $product->id,
        'quantity' => 10000,
        'unit_price' => 165,
        'currency' => 'RUB',
    ]);
    $color = Color::factory()->create(['name' => 'Белый']);
    $size = Size::factory()->create(['name' => 'S']);
    $density = Density::factory()->create(['name' => '180 gsm', 'gsm' => 180]);
    $product->colors()->attach($color);
    $product->sizes()->attach($size);
    $product->densities()->attach($density);

    $response = $this->get('/');

    $response->assertSee('$store.orderBuilder.addProduct', false);
    $response->assertSee('id: '.$product->id.',', false);
    $response->assertSee('moq: 10', false);
    $response->assertSee('от 165 ₽/шт', false);
    $response->assertSee('priceQuantities', false);
    $response->assertSee('name="`order_lines[${index}][product_id]`"', false);
    $response->assertSee('name="`order_lines[${index}][quantity]`"', false);
    $response->assertSee('name="`order_lines[${index}][density]`"', false);
    $response->assertSee('name="`order_lines[${index}][size]`"', false);
    $response->assertSee('name="`order_lines[${index}][color]`"', false);
    $response->assertSee('x-data=\'catalogFilter(', false);
    $response->assertSee('data-colors=', false);
    $response->assertSee('data-densities=', false);
    $response->assertSee('data-sizes=', false);
    $response->assertSee('value="'.$color->id.'"', false);
    $response->assertSee('value="'.$density->id.'"', false);
    $response->assertSee('value="'.$size->id.'"', false);
});

test('home page content resolves nested keys with a fallback', function () {
    $content = HomePageContent::create([
        'content' => ['hero' => ['headline_main' => 'База, на которой строятся']],
    ]);

    expect($content->get('hero.headline_main'))->toBe('База, на которой строятся');
    expect($content->get('form.consent'))->toBe('Согласен на обработку персональных данных');
    expect($content->get('hero.missing_key', 'default'))->toBe('default');
});

test('it renders consent copy for older homepage content records', function () {
    HomePageContent::query()->updateOrCreate(['id' => 1], [
        'content' => [
            'form' => [
                'phone' => 'ТЕЛЕФОН',
                'submit' => 'Сформировать заявку',
            ],
        ],
    ]);

    $response = $this->get('/');

    $response->assertSee('Согласен на обработку персональных данных', false);
    $response->assertSee('Политика конфиденциальности', false);
    $response->assertSee('Согласие на обработку', false);
});
