<?php

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductImage;
use Illuminate\Database\Migrations\Migration;

function useWebpProductPhotosMigration(): Migration
{
    return require database_path('migrations/2026_10_21_000002_use_webp_product_photos.php');
}

test('it points bundled jpg photos at their webp copies', function () {
    $product = Product::factory()->create(['cover_image' => '/brand/products/shopper.jpg']);
    $image = ProductImage::factory()->create(['product_id' => $product->id, 'path' => '/brand/products/hoodie-320-black.jpg']);

    useWebpProductPhotosMigration()->up();

    expect($product->fresh()->cover_image)->toBe('/brand/products/shopper.webp')
        ->and($image->fresh()->path)->toBe('/brand/products/hoodie-320-black.webp');
});

test('it leaves uploads and photos without a webp copy alone', function () {
    $upload = Product::factory()->create(['cover_image' => 'products/covers/admin-upload.jpg']);
    $unconverted = Product::factory()->create(['cover_image' => '/brand/products/does-not-exist.jpg']);
    $ogImage = Product::factory()->create(['og_image' => '/brand/model-close.jpg']);

    useWebpProductPhotosMigration()->up();

    expect($upload->fresh()->cover_image)->toBe('products/covers/admin-upload.jpg')
        ->and($unconverted->fresh()->cover_image)->toBe('/brand/products/does-not-exist.jpg')
        ->and($ogImage->fresh()->og_image)->toBe('/brand/model-close.jpg');
});
