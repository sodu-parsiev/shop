<?php

use App\Models\Catalog\Category;
use App\Models\Catalog\Color;
use App\Models\Catalog\CustomizationService;
use App\Models\Catalog\Density;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductPriceTier;
use App\Models\Catalog\Size;

test('a category has many products', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();

    expect($category->products)->toHaveCount(1)
        ->and($category->products->first()->is($product))->toBeTrue()
        ->and($product->category->is($category))->toBeTrue();
});

test('a color belongs to many products', function () {
    $color = Color::factory()->create();
    $product = Product::factory()->create();
    $product->colors()->attach($color);

    expect($color->products)->toHaveCount(1)
        ->and($color->products->first()->is($product))->toBeTrue()
        ->and($product->colors->first()->is($color))->toBeTrue();
});

test('a size belongs to many products', function () {
    $size = Size::factory()->create();
    $product = Product::factory()->create();
    $product->sizes()->attach($size);

    expect($size->products)->toHaveCount(1)
        ->and($size->products->first()->is($product))->toBeTrue()
        ->and($product->sizes->first()->is($size))->toBeTrue();
});

test('a density belongs to many products', function () {
    $density = Density::factory()->create();
    $product = Product::factory()->create();
    $product->densities()->attach($density);

    expect($density->products)->toHaveCount(1)
        ->and($density->products->first()->is($product))->toBeTrue()
        ->and($product->densities->first()->is($density))->toBeTrue();
});

test('a customization service belongs to many products', function () {
    $service = CustomizationService::factory()->create();
    $product = Product::factory()->create();

    $product->customizationServices()->attach($service);

    expect($service->products)->toHaveCount(1)
        ->and($service->products->first()->is($product))->toBeTrue()
        ->and($product->customizationServices->first()->is($service))->toBeTrue();
});

test('a product has many price tiers', function () {
    $product = Product::factory()->create();
    $tier = ProductPriceTier::factory()->create(['product_id' => $product->id]);

    expect($product->priceTiers)->toHaveCount(1)
        ->and($product->priceTiers->first()->is($tier))->toBeTrue()
        ->and($tier->product->is($product))->toBeTrue();
});
