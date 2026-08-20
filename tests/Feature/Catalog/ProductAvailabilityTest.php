<?php

use App\Enums\AvailabilityStatus;
use App\Models\Catalog\Color;
use App\Models\Catalog\Density;
use App\Models\Catalog\Product;

test('a product defaults to made to order and is not in stock', function () {
    $product = Product::factory()->create([
        'availability_status' => AvailabilityStatus::MadeToOrder,
        'stock_quantity' => null,
    ]);

    expect($product->isInStock())->toBeFalse();
});

test('a product marked in stock with zero quantity is not in stock', function () {
    $product = Product::factory()->create([
        'availability_status' => AvailabilityStatus::InStock,
        'stock_quantity' => 0,
    ]);

    expect($product->isInStock())->toBeFalse();
});

test('a product marked in stock with a positive quantity is in stock', function () {
    $product = Product::factory()->create([
        'availability_status' => AvailabilityStatus::InStock,
        'stock_quantity' => 10,
    ]);

    expect($product->isInStock())->toBeTrue();
});

test('colors are returned sorted by color sort order', function () {
    $product = Product::factory()->create();
    $colorA = Color::factory()->create(['sort_order' => 2]);
    $colorB = Color::factory()->create(['sort_order' => 1]);

    $product->colors()->attach([$colorA->id, $colorB->id]);

    $colors = $product->colors;

    expect($colors)->toHaveCount(2);
    expect($colors->first()->id)->toBe($colorB->id);
    expect($colors->last()->id)->toBe($colorA->id);
});

test('densities are returned sorted by gsm ascending', function () {
    $product = Product::factory()->create();
    $dense = Density::factory()->create(['gsm' => 240]);
    $light = Density::factory()->create(['gsm' => 180]);

    $product->densities()->attach([$dense->id, $light->id]);

    $densities = $product->densities;

    expect($densities)->toHaveCount(2);
    expect($densities->first()->id)->toBe($light->id);
    expect($densities->last()->id)->toBe($dense->id);
});
