<?php

use App\Filament\Resources\Catalog\Products\Pages\CreateProduct;
use App\Filament\Resources\Catalog\Products\Pages\EditProduct;
use App\Models\Catalog\Category;
use App\Models\Catalog\Color;
use App\Models\Catalog\Density;
use App\Models\Catalog\Product;
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

test('creating a product with checked colors, sizes, and densities persists the pivot rows', function () {
    $category = Category::factory()->create();
    $colors = Color::factory()->count(2)->create();
    $sizes = Size::factory()->count(2)->create();
    $densities = Density::factory()->count(2)->create();

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Variant Matrix Product',
            'sku' => 'SKU-VARIANT-MATRIX',
            'category_id' => $category->id,
            'colors' => $colors->pluck('id')->all(),
            'sizes' => $sizes->pluck('id')->all(),
            'densities' => $densities->pluck('id')->all(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::where('sku', 'SKU-VARIANT-MATRIX')->firstOrFail();

    expect($product->colors)->toHaveCount(2);
    expect($product->sizes)->toHaveCount(2);
    expect($product->densities)->toHaveCount(2);

    foreach ($colors as $color) {
        $this->assertDatabaseHas('product_color', ['product_id' => $product->id, 'color_id' => $color->id]);
    }
    foreach ($sizes as $size) {
        $this->assertDatabaseHas('product_size', ['product_id' => $product->id, 'size_id' => $size->id]);
    }
    foreach ($densities as $density) {
        $this->assertDatabaseHas('product_density', ['product_id' => $product->id, 'density_id' => $density->id]);
    }
});

test('the checkbox lists are pre-populated from a product\'s existing selections when editing', function () {
    $product = Product::factory()->create();
    $color = Color::factory()->create();
    $size = Size::factory()->create();
    $density = Density::factory()->create();

    $product->colors()->attach($color);
    $product->sizes()->attach($size);
    $product->densities()->attach($density);

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->assertFormSet([
            'colors' => [$color->id],
            'sizes' => [$size->id],
            'densities' => [$density->id],
        ]);
});

test('saving an edit with a changed selection syncs the pivot rows', function () {
    $product = Product::factory()->create();
    $colorA = Color::factory()->create();
    $colorB = Color::factory()->create();
    $size = Size::factory()->create();
    $density = Density::factory()->create();

    $product->colors()->attach($colorA);
    $product->sizes()->attach($size);
    $product->densities()->attach($density);

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm([
            'colors' => [$colorB->id],
            'sizes' => [$size->id],
            'densities' => [$density->id],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $product->refresh();

    expect($product->colors->pluck('id')->all())->toBe([$colorB->id]);
    $this->assertDatabaseMissing('product_color', ['product_id' => $product->id, 'color_id' => $colorA->id]);
    $this->assertDatabaseHas('product_color', ['product_id' => $product->id, 'color_id' => $colorB->id]);
});
