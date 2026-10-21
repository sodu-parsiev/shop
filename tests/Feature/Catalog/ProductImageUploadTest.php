<?php

use App\Filament\Resources\Catalog\Products\Pages\CreateProduct;
use App\Filament\Resources\Catalog\Products\Pages\EditProduct;
use App\Models\Catalog\Category;
use App\Models\Catalog\Color;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductImage;
use App\Models\User;
use App\Services\Images\WebpConverter;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Forms\Components\Select;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Administrator');
    $this->actingAs($admin);

    Storage::fake('public');
});

test('it can upload a cover image when creating a product', function () {
    $category = Category::factory()->create();

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Cover Image Product',
            'sku' => 'SKU-COVER',
            'category_id' => $category->id,
            'cover_image' => UploadedFile::fake()->image('cover.jpg'),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::where('sku', 'SKU-COVER')->firstOrFail();

    expect($product->cover_image)->not->toBeNull();
    Storage::disk('public')->assertExists($product->cover_image);
});

test('uploaded cover and gallery photos are stored as webp within the storefront size budget', function () {
    $category = Category::factory()->create();

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Webp Product',
            'sku' => 'SKU-WEBP',
            'category_id' => $category->id,
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 2400, 3000),
            'images' => [
                ['path' => [UploadedFile::fake()->image('gallery.png', 800, 1000)]],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::where('sku', 'SKU-WEBP')->firstOrFail();
    $cover = Storage::disk('public')->get($product->cover_image);
    $gallery = Storage::disk('public')->get($product->images()->value('path'));

    expect($product->cover_image)->toStartWith('products/covers/')->toEndWith('.webp')
        ->and(getimagesizefromstring($cover))->toMatchArray([0 => 1200, 1 => 1500, 'mime' => 'image/webp'])
        ->and(strlen($cover))->toBeLessThanOrEqual(WebpConverter::MAX_BYTES)
        ->and($product->images()->value('path'))->toStartWith('products/gallery/')->toEndWith('.webp')
        ->and(getimagesizefromstring($gallery))->toMatchArray([0 => 800, 1 => 1000, 'mime' => 'image/webp']);
});

test('the product form exposes a gallery images repeater backed by the images relationship', function () {
    $category = Category::factory()->create();

    Livewire::test(CreateProduct::class)
        ->assertFormFieldExists('images');
});

test('existing gallery images are loaded when editing a product', function () {
    $product = Product::factory()->create();
    ProductImage::factory()->count(2)->create(['product_id' => $product->id]);

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->assertFormSet(fn (array $state): bool => count($state['images']) === 2);
});

test('deleting a product cascades to delete its gallery images', function () {
    $product = Product::factory()->create();
    $images = ProductImage::factory()->count(2)->create(['product_id' => $product->id]);

    $product->delete();

    foreach ($images as $image) {
        $this->assertDatabaseMissing('product_images', ['id' => $image->id]);
    }
});

test('an admin can tag a gallery image with one of the product colors', function () {
    $white = Color::factory()->create(['name' => 'Белый', 'sort_order' => 1]);
    $black = Color::factory()->create(['name' => 'Чёрный', 'sort_order' => 2]);
    Color::factory()->create(['name' => 'Красный', 'sort_order' => 3]);
    $product = Product::factory()->create();
    $product->colors()->attach([$white->id, $black->id]);
    $image = ProductImage::factory()->create(['product_id' => $product->id]);
    Storage::disk('public')->put($image->path, 'image');

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->assertFormFieldExists(
            "images.record-{$image->id}.color_id",
            fn (Select $field): bool => $field->getOptions() === [$white->id => 'Белый', $black->id => 'Чёрный'],
        )
        ->set("data.images.record-{$image->id}.color_id", $black->id)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($image->fresh()->color_id)->toBe($black->id);
});

test('saving a product keeps seeded color photos that live in public/ rather than on the upload disk', function () {
    $black = Color::factory()->create(['name' => 'Чёрный']);
    $product = Product::factory()->create(['cover_image' => '/brand/products/basic-tee-140-150.webp']);
    $product->colors()->attach($black);
    $image = ProductImage::factory()->create([
        'product_id' => $product->id,
        'color_id' => $black->id,
        'path' => '/brand/products/basic-tee-175-185.webp',
    ]);

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($image->fresh())
        ->path->toBe('/brand/products/basic-tee-175-185.webp')
        ->color_id->toBe($black->id)
        ->and($product->fresh()->cover_image)->toBe('/brand/products/basic-tee-140-150.webp');
});
