<?php

use App\Enums\ProductStatus;
use App\Models\Catalog\Color;
use App\Models\Catalog\CustomizationService;
use App\Models\Catalog\Density;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductImage;
use App\Models\Catalog\ProductPriceTier;
use App\Models\Catalog\Size;
use App\Models\Content\HomePageContent;

test('an active product has a public detail page with gallery specs and product schema', function () {
    HomePageContent::query()->create(['content' => ['seo' => ['title' => 'Home']]]);
    $product = Product::factory()->create([
        'name' => 'Футболка Test',
        'slug' => 'test-tee',
        'sku' => 'SKU-TEST-TEE',
        'h1' => 'Футболка Test оптом',
        'short_description' => 'Короткое описание товара.',
        'description' => 'Полное описание товара.',
        'composition' => '100% хлопок',
        'fit' => 'Regular Fit',
        'size_table' => [
            ['size' => 'M', 'chest' => '52', 'length' => '70'],
        ],
        'status' => ProductStatus::Active,
        'cover_image' => '/brand/catalog-white-v2.jpg',
        'show_on_landing' => true,
    ]);
    ProductPriceTier::factory()->create([
        'product_id' => $product->id,
        'quantity' => 5000,
        'unit_price' => 170,
        'currency' => 'RUB',
    ]);
    $color = Color::factory()->create(['name' => 'Белый']);
    $density = Density::factory()->create(['name' => '180 gsm', 'gsm' => 180]);
    $size = Size::factory()->create(['name' => 'M']);
    $service = CustomizationService::factory()->create(['name' => 'Вышивка', 'description' => 'Нанесение логотипа.']);
    ProductImage::factory()->create([
        'product_id' => $product->id,
        'path' => '/brand/model-close.jpg',
        'alt_text' => 'Футболка крупно',
    ]);
    $product->colors()->attach($color);
    $product->densities()->attach($density);
    $product->sizes()->attach($size);
    $product->customizationServices()->attach($service);

    $response = $this->get('/catalog/test-tee');

    $response->assertOk();
    $response->assertSee('Футболка Test оптом');
    $response->assertSee('Короткое описание товара.');
    $response->assertSee('100% хлопок');
    $response->assertSee('от 170 ₽/шт', false);
    $response->assertSee('180 gsm');
    $response->assertSee('Размерная таблица');
    $response->assertSee('Вышивка');
    $response->assertSee('Product', false);
    $response->assertSee('BreadcrumbList', false);
    $response->assertDontSee('"Offer"', false);
});

test('inactive products are not publicly visible', function () {
    $product = Product::factory()->create([
        'slug' => 'inactive-tee',
        'status' => ProductStatus::Inactive,
    ]);

    $this->get($product->publicUrl())->assertNotFound();
});

test('products hidden from the landing are not publicly visible', function () {
    $product = Product::factory()->create([
        'slug' => 'hidden-tee',
        'status' => ProductStatus::Active,
        'show_on_landing' => false,
    ]);

    $this->get($product->publicUrl())->assertNotFound();
});

test('the product page renders a picker only for axes that have attached options', function () {
    HomePageContent::query()->create(['content' => ['seo' => ['title' => 'Home']]]);
    $product = Product::factory()->create([
        'slug' => 'only-sizes-tee',
        'status' => ProductStatus::Active,
        'show_on_landing' => true,
    ]);
    $product->sizes()->attach(Size::factory()->create(['name' => 'M']));

    $response = $this->get($product->publicUrl());

    $response->assertOk();
    $response->assertSee('x-model="selectedSizes"', false);
    $response->assertSee('value="M"', false);
    $response->assertDontSee('x-model="selectedColors"', false);
    $response->assertDontSee('x-model="selectedDensities"', false);
});

test('the product page renders no picker when the product has no attached options', function () {
    HomePageContent::query()->create(['content' => ['seo' => ['title' => 'Home']]]);
    $product = Product::factory()->create([
        'slug' => 'no-options-tee',
        'status' => ProductStatus::Active,
        'show_on_landing' => true,
    ]);

    $response = $this->get($product->publicUrl());

    $response->assertOk();
    $response->assertDontSee('Отметьте цвет, размер и плотность', false);
});

test('an inactive color attached to a product is not offered in the picker', function () {
    HomePageContent::query()->create(['content' => ['seo' => ['title' => 'Home']]]);
    $product = Product::factory()->create([
        'slug' => 'inactive-color-tee',
        'status' => ProductStatus::Active,
        'show_on_landing' => true,
    ]);
    $product->colors()->attach(Color::factory()->create(['name' => 'Активный цвет', 'is_active' => true]));
    $product->colors()->attach(Color::factory()->create(['name' => 'Скрытый цвет', 'is_active' => false]));

    $response = $this->get($product->publicUrl());

    $response->assertOk();
    $response->assertSee('value="Активный цвет"', false);
    $response->assertDontSee('value="Скрытый цвет"', false);
});
