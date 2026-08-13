<?php

use App\Enums\AvailabilityStatus;
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

test('stock_quantity is hidden by default because the default availability is made to order', function () {
    $product = Product::factory()->create();

    Livewire::test(ProductVariantsRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => EditProduct::class,
    ])
        ->mountTableAction('create')
        ->assertFormFieldHidden('stock_quantity');
});

test('stock_quantity becomes visible once availability is switched to in stock', function () {
    $product = Product::factory()->create();

    Livewire::test(ProductVariantsRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => EditProduct::class,
    ])
        ->mountTableAction('create')
        ->setTableActionData(['availability_status' => AvailabilityStatus::InStock->value])
        ->assertFormFieldVisible('stock_quantity');
});

test('switching an existing variant to made to order clears its stock quantity', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'availability_status' => AvailabilityStatus::InStock,
        'stock_quantity' => 75,
    ]);

    Livewire::test(ProductVariantsRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => EditProduct::class,
    ])
        ->mountTableAction('edit', record: $variant)
        ->setTableActionData(['availability_status' => AvailabilityStatus::MadeToOrder->value])
        ->callMountedTableAction()
        ->assertHasNoTableActionErrors();

    expect($variant->refresh())
        ->availability_status->toBe(AvailabilityStatus::MadeToOrder)
        ->stock_quantity->toBeNull();
});

test('a new in-stock variant persists its stock quantity', function () {
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
            'availability_status' => AvailabilityStatus::InStock->value,
            'stock_quantity' => 12,
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('product_variants', [
        'product_id' => $product->id,
        'color_id' => $color->id,
        'availability_status' => AvailabilityStatus::InStock->value,
        'stock_quantity' => 12,
    ]);
});
