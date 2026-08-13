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
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Administrator');
    $this->actingAs($admin);
});

test('it can create a product variant from the product edit page', function () {
    $product = Product::factory()->create();
    $color = Color::factory()->create();
    $size = Size::factory()->create();
    $density = Density::factory()->create();

    Livewire::test(ProductVariantsRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => EditProduct::class,
    ])
        ->callTableAction('create', data: [
            'color_id' => $color->id,
            'size_id' => $size->id,
            'density_id' => $density->id,
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('product_variants', [
        'product_id' => $product->id,
        'color_id' => $color->id,
        'size_id' => $size->id,
        'density_id' => $density->id,
    ]);
});

test('it lists existing variants for the product', function () {
    $product = Product::factory()->create();
    $variants = ProductVariant::factory()->count(2)->create(['product_id' => $product->id]);

    Livewire::test(ProductVariantsRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => EditProduct::class,
    ])
        ->assertCanSeeTableRecords($variants);
});
