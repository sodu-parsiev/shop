<?php

use App\Filament\Resources\Catalog\Categories\Pages\ManageCategories;
use App\Filament\Resources\Catalog\Colors\Pages\ManageColors;
use App\Filament\Resources\Catalog\CustomizationServices\Pages\ManageCustomizationServices;
use App\Filament\Resources\Catalog\Densities\Pages\ManageDensities;
use App\Filament\Resources\Catalog\Sizes\Pages\ManageSizes;
use App\Models\Catalog\Category;
use App\Models\Catalog\Color;
use App\Models\Catalog\CustomizationService;
use App\Models\Catalog\Density;
use App\Models\Catalog\Product;
use App\Models\Catalog\Size;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Administrator');
    $this->actingAs($admin);
});

dataset('dictionary delete guards', [
    'category' => [
        ManageCategories::class,
        fn () => Category::factory()->create(),
        fn (Category $category) => Product::factory()->create(['category_id' => $category->id]),
    ],
    'color' => [
        ManageColors::class,
        fn () => Color::factory()->create(),
        fn (Color $color) => Product::factory()->create()->colors()->attach($color),
    ],
    'size' => [
        ManageSizes::class,
        fn () => Size::factory()->create(),
        fn (Size $size) => Product::factory()->create()->sizes()->attach($size),
    ],
    'density' => [
        ManageDensities::class,
        fn () => Density::factory()->create(),
        fn (Density $density) => Product::factory()->create()->densities()->attach($density),
    ],
    'customization service' => [
        ManageCustomizationServices::class,
        fn () => CustomizationService::factory()->create(),
        function (CustomizationService $service) {
            $product = Product::factory()->create();
            $product->customizationServices()->attach($service);

            return $product;
        },
    ],
]);

test('deleting an unreferenced dictionary value succeeds', function (string $page, Closure $makeRecord, Closure $makeReference) {
    $record = $makeRecord();

    Livewire::test($page)
        ->callTableAction('delete', record: $record);

    $this->assertModelMissing($record);
})->with('dictionary delete guards');

test('the row delete action is disabled for a dictionary value referenced by a product or variant', function (string $page, Closure $makeRecord, Closure $makeReference) {
    $record = $makeRecord();
    $makeReference($record);

    Livewire::test($page)
        ->assertTableActionDisabled('delete', record: $record)
        ->callTableAction('delete', record: $record);

    $this->assertModelExists($record);
})->with('dictionary delete guards');

test('bulk deleting a dictionary value referenced by a product or variant is blocked with a notification', function (string $page, Closure $makeRecord, Closure $makeReference) {
    $record = $makeRecord();
    $makeReference($record);

    Livewire::test($page)
        ->callTableBulkAction('delete', records: [$record])
        ->assertNotified();

    $this->assertModelExists($record);
})->with('dictionary delete guards');

test('a raw database delete of an in-use dictionary value is rejected by the foreign key', function () {
    $category = Category::factory()->create();
    Product::factory()->create(['category_id' => $category->id]);

    expect(fn () => DB::table('categories')->where('id', $category->id)->delete())
        ->toThrow(QueryException::class);
});

test('a raw database delete of an in-use color is rejected by the foreign key', function () {
    $color = Color::factory()->create();
    $product = Product::factory()->create();
    $product->colors()->attach($color);

    expect(fn () => DB::table('colors')->where('id', $color->id)->delete())
        ->toThrow(QueryException::class);
});
