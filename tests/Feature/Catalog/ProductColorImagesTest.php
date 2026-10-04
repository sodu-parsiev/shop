<?php

use App\Models\Catalog\Color;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductImage;

test('a product image url resolves seeded, uploaded and remote paths', function () {
    expect(ProductImage::make(['path' => '/brand/products/basic-tee-175-185.jpg'])->url())
        ->toBe(asset('brand/products/basic-tee-175-185.jpg'))
        ->and(ProductImage::make(['path' => 'products/gallery/black.png'])->url())
        ->toBe(asset('storage/products/gallery/black.png'))
        ->and(ProductImage::make(['path' => 'https://cdn.example.com/black.jpg'])->url())
        ->toBe('https://cdn.example.com/black.jpg');
});

test('color image urls map each offered color to its first tagged gallery photo', function () {
    $product = Product::factory()->create();
    $white = Color::factory()->create(['name' => 'Белый', 'sort_order' => 1]);
    $black = Color::factory()->create(['name' => 'Чёрный', 'sort_order' => 2]);
    $product->colors()->attach([$white->id, $black->id]);

    ProductImage::factory()->create(['product_id' => $product->id, 'path' => '/brand/untagged.jpg', 'sort_order' => 0]);
    ProductImage::factory()->create(['product_id' => $product->id, 'color_id' => $black->id, 'path' => '/brand/black-front.jpg', 'sort_order' => 1]);
    ProductImage::factory()->create(['product_id' => $product->id, 'color_id' => $black->id, 'path' => '/brand/black-back.jpg', 'sort_order' => 2]);

    expect($product->fresh()->colorImageUrls())->toBe([
        'Чёрный' => asset('brand/black-front.jpg'),
    ]);
});

test('color image urls ignore photos tagged with a color the product is not sold in', function () {
    $product = Product::factory()->create();
    $black = Color::factory()->create(['name' => 'Чёрный']);
    $red = Color::factory()->create(['name' => 'Красный']);
    $product->colors()->attach($black);

    ProductImage::factory()->create(['product_id' => $product->id, 'color_id' => $red->id, 'path' => '/brand/red.jpg']);

    expect($product->fresh()->colorImageUrls())->toBe([]);
});
