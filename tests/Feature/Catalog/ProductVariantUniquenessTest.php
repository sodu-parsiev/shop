<?php

use App\Filament\Resources\Catalog\Products\Pages\EditProduct;
use App\Filament\Resources\Catalog\Products\RelationManagers\ProductVariantsRelationManager;
use App\Models\Catalog\Color;
use App\Models\Catalog\Density;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use App\Models\Catalog\Size;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\QueryException;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Administrator');
    $this->actingAs($admin);
});

test('the database rejects a duplicate product+color+size+density combination', function () {
    $product = Product::factory()->create();
    $color = Color::factory()->create();
    $size = Size::factory()->create();
    $density = Density::factory()->create();

    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'color_id' => $color->id,
        'size_id' => $size->id,
        'density_id' => $density->id,
    ]);

    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'color_id' => $color->id,
        'size_id' => $size->id,
        'density_id' => $density->id,
    ]);
})->throws(QueryException::class);

test('the admin form rejects a duplicate product+color+size+density combination', function () {
    $product = Product::factory()->create();
    $color = Color::factory()->create();
    $size = Size::factory()->create();
    $density = Density::factory()->create();

    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'color_id' => $color->id,
        'size_id' => $size->id,
        'density_id' => $density->id,
    ]);

    Livewire::test(ProductVariantsRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => EditProduct::class,
    ])
        ->callTableAction('create', data: [
            'color_id' => $color->id,
            'size_id' => $size->id,
            'density_id' => $density->id,
        ])
        ->assertHasTableActionErrors(['color_id' => 'unique']);

    expect(ProductVariant::where([
        'product_id' => $product->id,
        'color_id' => $color->id,
        'size_id' => $size->id,
        'density_id' => $density->id,
    ])->count())->toBe(1);
});

test('the same color/size/density combination is allowed for a different product', function () {
    $color = Color::factory()->create();
    $size = Size::factory()->create();
    $density = Density::factory()->create();

    $variant = ProductVariant::factory()->create([
        'color_id' => $color->id,
        'size_id' => $size->id,
        'density_id' => $density->id,
    ]);

    $otherProduct = Product::factory()->create();

    $otherVariant = ProductVariant::factory()->create([
        'product_id' => $otherProduct->id,
        'color_id' => $color->id,
        'size_id' => $size->id,
        'density_id' => $density->id,
    ]);

    expect($otherVariant->id)->not->toBe($variant->id);
});
