<?php

use App\Filament\Resources\Catalog\Products\Pages\CreateProduct;
use App\Filament\Resources\Catalog\Products\Pages\EditProduct;
use App\Filament\Resources\Catalog\Products\Pages\ListProducts;
use App\Models\Catalog\Category;
use App\Models\Catalog\CustomizationService;
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

test('it can list products', function () {
    $products = Product::factory()->count(3)->create();

    Livewire::test(ListProducts::class)
        ->assertCanSeeTableRecords($products);
});

test('it can create a product with a category and customization services', function () {
    $category = Category::factory()->create();
    $services = CustomizationService::factory()->count(2)->create();

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Custom Tote Bag',
            'category_id' => $category->id,
            'customizationServices' => $services->pluck('id')->all(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('products', [
        'name' => 'Custom Tote Bag',
        'category_id' => $category->id,
    ]);

    $product = Product::where('name', 'Custom Tote Bag')->firstOrFail();

    expect($product->customizationServices)->toHaveCount(2);

    foreach ($services as $service) {
        $this->assertDatabaseHas('product_customization_service', [
            'product_id' => $product->id,
            'customization_service_id' => $service->id,
        ]);
    }
});

test('it requires a category when creating a product', function () {
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'No Category Product',
            'category_id' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['category_id' => 'required']);
});

test('it can update a product', function () {
    $originalCategory = Category::factory()->create();
    $newCategory = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $originalCategory->id]);

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm([
            'name' => 'Renamed Product',
            'category_id' => $newCategory->id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Renamed Product',
        'category_id' => $newCategory->id,
    ]);
});

test('the product index, create, and edit pages render over HTTP', function () {
    $product = Product::factory()->create();

    $this->get('/admin/catalog/products')->assertOk();
    $this->get('/admin/catalog/products/create')->assertOk();
    $this->get("/admin/catalog/products/{$product->id}/edit")->assertOk();
});
