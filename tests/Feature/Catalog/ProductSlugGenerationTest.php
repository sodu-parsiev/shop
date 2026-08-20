<?php

use App\Models\Catalog\Product;
use Illuminate\Support\Str;

test('the slug is generated from the name and sku when not provided', function () {
    $product = Product::factory()->create([
        'name' => 'Custom Tote Bag',
        'sku' => 'SKU-CUSTOM-TOTE',
        'slug' => null,
    ]);

    expect($product->slug)->toBe(Str::slug('Custom Tote Bag-SKU-CUSTOM-TOTE'));
});

test('an explicitly provided slug is preserved', function () {
    $product = Product::factory()->create(['slug' => 'my-explicit-slug']);

    expect($product->slug)->toBe('my-explicit-slug');
});

test('the slug does not change when the product is renamed', function () {
    $product = Product::factory()->create(['name' => 'Original Name']);
    $originalSlug = $product->slug;

    $product->update(['name' => 'Renamed Product']);

    expect($product->fresh()->slug)->toBe($originalSlug);
});

test('cyrillic product names are transliterated into a readable slug', function () {
    $product = Product::factory()->create([
        'name' => 'Базовая футболка',
        'sku' => 'SKU-BAZOVAYA',
        'slug' => null,
    ]);

    expect($product->slug)->toBe(Str::slug('Базовая футболка-SKU-BAZOVAYA'));
});
