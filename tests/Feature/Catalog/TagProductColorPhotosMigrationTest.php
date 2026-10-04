<?php

use App\Models\Catalog\Color;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductImage;
use Illuminate\Database\Migrations\Migration;

function tagProductColorPhotosMigration(): Migration
{
    return require database_path('migrations/2026_10_04_000001_tag_product_color_photos.php');
}

beforeEach(function () {
    $this->white = Color::factory()->create(['name' => 'Белый', 'sort_order' => 1]);
    $this->black = Color::factory()->create(['name' => 'Чёрный', 'sort_order' => 2]);
});

test('it tags each product color with its photo and swaps covers that show an unsold color', function () {
    $oversize = Product::factory()->create(['sku' => 'SH-TEE-OVR-180', 'cover_image' => '/brand/products/oversize-tee-180.jpg']);
    $hoodie = Product::factory()->create(['sku' => 'SH-HOODIE-3T-270', 'cover_image' => '/brand/products/hoodie-three-thread-260-280.jpg']);
    $oversize->colors()->attach([$this->white->id, $this->black->id]);
    $hoodie->colors()->attach($this->black);

    tagProductColorPhotosMigration()->up();

    expect($oversize->fresh()->load('colors', 'images')->colorImageUrls())->toBe([
        'Белый' => asset('brand/products/oversize-tee-180-white.jpg'),
        'Чёрный' => asset('brand/products/oversize-tee-180-black.jpg'),
    ])
        ->and($oversize->fresh()->cover_image)->toBe('/brand/products/oversize-tee-180-white.jpg')
        ->and($hoodie->fresh()->cover_image)->toBe('/brand/products/hoodie-320-black.jpg');
});

test('it keeps covers and color photos an admin already set, and is safe to run twice', function () {
    $longsleeve = Product::factory()->create(['sku' => 'SH-LONG-145', 'cover_image' => 'products/covers/admin-upload.jpg']);
    ProductImage::factory()->create([
        'product_id' => $longsleeve->id,
        'color_id' => $this->black->id,
        'path' => 'products/gallery/admin-black.jpg',
    ]);

    tagProductColorPhotosMigration()->up();
    tagProductColorPhotosMigration()->up();

    expect($longsleeve->fresh()->cover_image)->toBe('products/covers/admin-upload.jpg')
        ->and(ProductImage::where('product_id', $longsleeve->id)->where('color_id', $this->black->id)->pluck('path')->all())
        ->toBe(['products/gallery/admin-black.jpg'])
        ->and(ProductImage::where('product_id', $longsleeve->id)->where('color_id', $this->white->id)->count())
        ->toBe(1);
});

test('rolling back restores the seeded cover and removes only the photos it added', function () {
    $oversize = Product::factory()->create(['sku' => 'SH-TEE-OVR-180', 'cover_image' => '/brand/products/oversize-tee-180.jpg']);
    $untouched = ProductImage::factory()->create(['product_id' => $oversize->id, 'path' => 'products/gallery/extra.jpg']);

    tagProductColorPhotosMigration()->up();
    tagProductColorPhotosMigration()->down();

    expect($oversize->fresh()->cover_image)->toBe('/brand/products/oversize-tee-180.jpg')
        ->and(ProductImage::where('product_id', $oversize->id)->pluck('id')->all())->toBe([$untouched->id]);
});

test('every photo the migration links exists in public/', function () {
    $migration = new ReflectionClass(tagProductColorPhotosMigration());
    $paths = collect($migration->getConstant('SKU_COLOR_PHOTOS'))->flatten()
        ->merge(collect($migration->getConstant('SKU_COVER_SWAPS'))->flatten());

    $paths->each(fn (string $path) => expect(public_path(ltrim($path, '/')))->toBeFile());
});
