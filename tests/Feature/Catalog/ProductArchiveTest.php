<?php

use App\Enums\ProductStatus;
use App\Filament\Resources\Catalog\Products\Pages\EditProduct;
use App\Filament\Resources\Catalog\Products\Pages\ListProducts;
use App\Models\Catalog\Product;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Administrator');
    $this->actingAs($admin);
});

test('an active product can be archived from the edit page', function () {
    $product = Product::factory()->create(['status' => ProductStatus::Active]);

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->assertActionVisible('archive')
        ->assertActionHidden('restore')
        ->callAction('archive');

    expect($product->refresh()->status)->toBe(ProductStatus::Inactive);
});

test('an archived product can be restored from the edit page', function () {
    $product = Product::factory()->create(['status' => ProductStatus::Inactive]);

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->assertActionVisible('restore')
        ->assertActionHidden('archive')
        ->callAction('restore');

    expect($product->refresh()->status)->toBe(ProductStatus::Active);
});

test('a product can be archived from the products table row action', function () {
    $product = Product::factory()->create(['status' => ProductStatus::Active]);

    Livewire::test(ListProducts::class)
        ->callTableAction('archive', $product);

    expect($product->refresh()->status)->toBe(ProductStatus::Inactive);
});

test('products can be bulk archived from the products table', function () {
    $products = Product::factory()->count(2)->create(['status' => ProductStatus::Active]);

    Livewire::test(ListProducts::class)
        ->callTableBulkAction('archive', $products);

    expect($products->fresh()->pluck('status')->all())
        ->each(fn ($status) => $status->toBe(ProductStatus::Inactive));
});
