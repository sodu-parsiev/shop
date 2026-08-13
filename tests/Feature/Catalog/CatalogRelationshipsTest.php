<?php

use App\Models\Catalog\Category;
use App\Models\Catalog\Color;
use App\Models\Catalog\CustomizationService;
use App\Models\Catalog\Density;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use App\Models\Catalog\Size;

test('a category has many products', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();

    expect($category->products)->toHaveCount(1)
        ->and($category->products->first()->is($product))->toBeTrue()
        ->and($product->category->is($category))->toBeTrue();
});

test('a color has many product variants', function () {
    $color = Color::factory()->create();
    $variant = ProductVariant::factory()->create(['color_id' => $color->id]);

    expect($color->variants)->toHaveCount(1)
        ->and($color->variants->first()->is($variant))->toBeTrue()
        ->and($variant->color->is($color))->toBeTrue();
});

test('a size has many product variants', function () {
    $size = Size::factory()->create();
    $variant = ProductVariant::factory()->create(['size_id' => $size->id]);

    expect($size->variants)->toHaveCount(1)
        ->and($size->variants->first()->is($variant))->toBeTrue()
        ->and($variant->size->is($size))->toBeTrue();
});

test('a density has many product variants', function () {
    $density = Density::factory()->create();
    $variant = ProductVariant::factory()->create(['density_id' => $density->id]);

    expect($density->variants)->toHaveCount(1)
        ->and($density->variants->first()->is($variant))->toBeTrue()
        ->and($variant->density->is($density))->toBeTrue();
});

test('a customization service belongs to many products', function () {
    $service = CustomizationService::factory()->create();
    $product = Product::factory()->create();

    $product->customizationServices()->attach($service);

    expect($service->products)->toHaveCount(1)
        ->and($service->products->first()->is($product))->toBeTrue()
        ->and($product->customizationServices->first()->is($service))->toBeTrue();
});

test('a product variant resolves its product, color, size, and density', function () {
    $variant = ProductVariant::factory()->create();

    expect($variant->product)->toBeInstanceOf(Product::class)
        ->and($variant->color)->toBeInstanceOf(Color::class)
        ->and($variant->size)->toBeInstanceOf(Size::class)
        ->and($variant->density)->toBeInstanceOf(Density::class);
});

test('a product has many variants', function () {
    $product = Product::factory()->create();
    ProductVariant::factory()->count(2)->create(['product_id' => $product->id]);

    expect($product->variants)->toHaveCount(2);
});
