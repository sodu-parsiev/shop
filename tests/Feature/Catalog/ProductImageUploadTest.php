<?php

use App\Filament\Resources\Catalog\Products\Pages\CreateProduct;
use App\Filament\Resources\Catalog\Products\Pages\EditProduct;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductImage;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
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
