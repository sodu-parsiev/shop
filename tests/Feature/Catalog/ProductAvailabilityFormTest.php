<?php

use App\Enums\AvailabilityStatus;
use App\Filament\Resources\Catalog\Products\Pages\CreateProduct;
use App\Filament\Resources\Catalog\Products\Pages\EditProduct;
use App\Models\Catalog\Category;
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

test('stock_quantity is hidden by default because the default availability is made to order', function () {
    $category = Category::factory()->create();

    Livewire::test(CreateProduct::class)
        ->fillForm(['category_id' => $category->id])
        ->assertFormFieldHidden('stock_quantity');
});

test('stock_quantity becomes visible once availability is switched to in stock', function () {
    $category = Category::factory()->create();

    Livewire::test(CreateProduct::class)
        ->fillForm(['category_id' => $category->id, 'availability_status' => AvailabilityStatus::InStock->value])
        ->assertFormFieldVisible('stock_quantity');
});

test('switching an existing product to made to order clears its stock quantity', function () {
    $product = Product::factory()->create([
        'availability_status' => AvailabilityStatus::InStock,
        'stock_quantity' => 75,
    ]);

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm(['availability_status' => AvailabilityStatus::MadeToOrder->value])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->refresh())
        ->availability_status->toBe(AvailabilityStatus::MadeToOrder)
        ->stock_quantity->toBeNull();
});

test('a new in-stock product persists its stock quantity', function () {
    $category = Category::factory()->create();

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'In Stock Product',
            'sku' => 'SKU-IN-STOCK',
            'category_id' => $category->id,
            'availability_status' => AvailabilityStatus::InStock->value,
            'stock_quantity' => 12,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('products', [
        'sku' => 'SKU-IN-STOCK',
        'availability_status' => AvailabilityStatus::InStock->value,
        'stock_quantity' => 12,
    ]);
});
