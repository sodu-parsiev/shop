<?php

use App\Enums\ProductStatus;
use App\Filament\Resources\Catalog\Products\Pages\CreateProduct;
use App\Filament\Resources\Catalog\Products\Pages\EditProduct;
use App\Filament\Resources\Catalog\Products\Pages\ListProducts;
use App\Models\Catalog\Category;
use App\Models\Catalog\CustomizationService;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductPriceTier;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Str;
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
            'sku' => 'SKU-CUSTOM-TOTE',
            'category_id' => $category->id,
            'customizationServices' => $services->pluck('id')->all(),
            'moq' => 10,
            'status' => ProductStatus::Active->value,
            'featured' => true,
            'show_on_landing' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('products', [
        'name' => 'Custom Tote Bag',
        'sku' => 'SKU-CUSTOM-TOTE',
        'slug' => Str::slug('Custom Tote Bag-SKU-CUSTOM-TOTE'),
        'category_id' => $category->id,
        'moq' => 10,
        'status' => ProductStatus::Active->value,
        'featured' => true,
        'show_on_landing' => true,
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

test('it can create a product with price tiers', function () {
    $category = Category::factory()->create();

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Priced Product',
            'sku' => 'SKU-PRICED',
            'category_id' => $category->id,
            'priceTiers' => [
                [
                    'quantity' => 10000,
                    'unit_price' => 165,
                    'currency' => ProductPriceTier::DEFAULT_CURRENCY,
                ],
                [
                    'quantity' => 10,
                    'unit_price' => 190,
                    'currency' => ProductPriceTier::DEFAULT_CURRENCY,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::where('sku', 'SKU-PRICED')->firstOrFail();

    $this->assertDatabaseHas('product_price_tiers', [
        'product_id' => $product->id,
        'quantity' => 10000,
        'unit_price' => 165,
        'currency' => ProductPriceTier::DEFAULT_CURRENCY,
    ]);

    $this->assertDatabaseHas('product_price_tiers', [
        'product_id' => $product->id,
        'quantity' => 10,
        'unit_price' => 190,
        'currency' => ProductPriceTier::DEFAULT_CURRENCY,
    ]);
});

test('it requires a category when creating a product', function () {
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'No Category Product',
            'sku' => 'SKU-NO-CATEGORY',
            'category_id' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['category_id' => 'required']);
});

test('it requires a unique sku when creating a product', function () {
    $category = Category::factory()->create();
    $existing = Product::factory()->create(['sku' => 'SKU-EXISTING']);

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Another Product',
            'sku' => $existing->sku,
            'category_id' => $category->id,
        ])
        ->call('create')
        ->assertHasFormErrors(['sku' => 'unique']);
});

test('it requires an moq of at least 1 when creating a product', function () {
    $category = Category::factory()->create();

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Low MOQ Product',
            'sku' => 'SKU-LOW-MOQ',
            'category_id' => $category->id,
            'moq' => 0,
        ])
        ->call('create')
        ->assertHasFormErrors(['moq' => 'min']);
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
