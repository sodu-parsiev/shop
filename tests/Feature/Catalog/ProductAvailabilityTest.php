<?php

use App\Enums\AvailabilityStatus;
use App\Models\Catalog\Color;
use App\Models\Catalog\Density;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;

test('a product with no variants is not in stock', function () {
    $product = Product::factory()->create();

    expect($product->isInStock())->toBeFalse();
});

test('a product with only made-to-order variants is not in stock', function () {
    $product = Product::factory()->create();
    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'availability_status' => AvailabilityStatus::MadeToOrder,
        'stock_quantity' => null,
    ]);

    expect($product->fresh()->isInStock())->toBeFalse();
});

test('a product with an in-stock variant but zero quantity is not in stock', function () {
    $product = Product::factory()->create();
    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'availability_status' => AvailabilityStatus::InStock,
        'stock_quantity' => 0,
    ]);

    expect($product->fresh()->isInStock())->toBeFalse();
});

test('a product with at least one in-stock variant with quantity is in stock', function () {
    $product = Product::factory()->create();
    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'availability_status' => AvailabilityStatus::MadeToOrder,
        'stock_quantity' => null,
    ]);
    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'availability_status' => AvailabilityStatus::InStock,
        'stock_quantity' => 10,
    ]);

    expect($product->fresh()->isInStock())->toBeTrue();
});

test('distinct colors are deduplicated and sorted by color sort order', function () {
    $product = Product::factory()->create();
    $colorA = Color::factory()->create(['sort_order' => 2]);
    $colorB = Color::factory()->create(['sort_order' => 1]);

    ProductVariant::factory()->create(['product_id' => $product->id, 'color_id' => $colorA->id]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'color_id' => $colorA->id]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'color_id' => $colorB->id]);

    $colors = $product->fresh()->load('variants.color')->distinctColors();

    expect($colors)->toHaveCount(2);
    expect($colors->first()->id)->toBe($colorB->id);
    expect($colors->last()->id)->toBe($colorA->id);
});

test('distinct densities are deduplicated and sorted by gsm ascending', function () {
    $product = Product::factory()->create();
    $dense = Density::factory()->create(['gsm' => 240]);
    $light = Density::factory()->create(['gsm' => 180]);

    ProductVariant::factory()->create(['product_id' => $product->id, 'density_id' => $dense->id]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'density_id' => $dense->id]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'density_id' => $light->id]);

    $densities = $product->fresh()->load('variants.density')->distinctDensities();

    expect($densities)->toHaveCount(2);
    expect($densities->first()->id)->toBe($light->id);
    expect($densities->last()->id)->toBe($dense->id);
});
