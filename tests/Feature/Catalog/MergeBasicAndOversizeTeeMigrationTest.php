<?php

use App\Enums\ProductStatus;
use App\Models\Catalog\Category;
use App\Models\Catalog\Density;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductPriceTier;
use App\Models\Content\Redirect;
use Illuminate\Support\Facades\DB;

/**
 * Simulates the pre-merge production shape: 5 separate single-density products,
 * their price tiers still carrying density_id = null (the state right after the
 * schema migration lands but before this data migration runs).
 */
function seedPreMergeTeeFamilies(): void
{
    $category = Category::factory()->create(['name' => 'Футболки']);

    $density140 = Density::factory()->create(['name' => '140-150 гр', 'gsm' => 145]);
    $density160 = Density::factory()->create(['name' => '160 гр', 'gsm' => 160]);
    $density180 = Density::factory()->create(['name' => '180 гр', 'gsm' => 180]);
    $density220 = Density::factory()->create(['name' => '220 гр', 'gsm' => 230]);

    $rows = [
        ['basic-tee-140-150', 'Базовая футболка 140-150 гр', $density140, 2.13],
        ['basic-tee-155-165', 'Базовая футболка 160 гр', $density160, 2.38],
        ['basic-tee-175-185', 'Базовая футболка 180 гр', $density180, 2.63],
        ['oversize-tee-180', 'Оверсайз футболка 180 гр', $density180, 3.38],
        ['oversize-tee-220-240', 'Оверсайз футболка 220 гр', $density220, 4.00],
    ];

    foreach ($rows as [$slug, $name, $density, $price]) {
        $product = Product::factory()->create([
            'slug' => $slug,
            'name' => $name,
            'category_id' => $category->id,
            'status' => ProductStatus::Active,
            'show_on_landing' => true,
        ]);
        $product->densities()->attach($density->id);

        ProductPriceTier::factory()->create([
            'product_id' => $product->id,
            'density_id' => null,
            'quantity' => 5000,
            'unit_price' => $price,
        ]);
    }
}

test('the merge migration consolidates the pre-existing tee families into two variant products', function () {
    seedPreMergeTeeFamilies();

    $migration = include database_path('migrations/2026_09_14_000001_merge_basic_and_oversize_tee_density_variants.php');
    $migration->up();

    $basicTee = Product::query()->with(['densities', 'priceTiers'])->where('slug', 'basic-tee-140-150')->firstOrFail();
    expect($basicTee->densities->pluck('name')->sort()->values()->all())->toBe(['140-150 гр', '160 гр', '180 гр']);
    expect($basicTee->priceTiers->pluck('density_id'))->not->toContain(null);
    expect($basicTee->priceTierForQuantity(5000, Density::where('name', '140-150 гр')->value('id'))->unit_price)->toBe('2.13');
    expect($basicTee->priceTierForQuantity(5000, Density::where('name', '160 гр')->value('id'))->unit_price)->toBe('2.38');
    expect($basicTee->priceTierForQuantity(5000, Density::where('name', '180 гр')->value('id'))->unit_price)->toBe('2.63');

    $oversizeTee = Product::query()->with(['densities', 'priceTiers'])->where('slug', 'oversize-tee-180')->firstOrFail();
    expect($oversizeTee->densities->pluck('name')->sort()->values()->all())->toBe(['180 гр', '220 гр']);
    expect($oversizeTee->priceTierForQuantity(5000, Density::where('name', '220 гр')->value('id'))->unit_price)->toBe('4.00');

    foreach (['basic-tee-155-165', 'basic-tee-175-185', 'oversize-tee-220-240'] as $slug) {
        $retired = Product::query()->with('priceTiers')->where('slug', $slug)->firstOrFail();
        expect($retired->status)->toBe(ProductStatus::Inactive);
        expect($retired->show_on_landing)->toBeFalse();
        expect($retired->priceTiers)->toHaveCount(0);
        expect(DB::table('product_density')->where('product_id', $retired->id)->exists())->toBeFalse();
    }

    expect(Redirect::where('source_path', '/catalog/basic-tee-155-165')->value('target_url'))->toBe('/catalog/basic-tee-140-150');
    expect(Redirect::where('source_path', '/catalog/basic-tee-175-185')->value('target_url'))->toBe('/catalog/basic-tee-140-150');
    expect(Redirect::where('source_path', '/catalog/oversize-tee-220-240')->value('target_url'))->toBe('/catalog/oversize-tee-180');
});

test('the merge migration is idempotent', function () {
    seedPreMergeTeeFamilies();

    $migration = include database_path('migrations/2026_09_14_000001_merge_basic_and_oversize_tee_density_variants.php');
    $migration->up();
    $migration->up();

    $basicTee = Product::query()->with(['densities', 'priceTiers'])->where('slug', 'basic-tee-140-150')->firstOrFail();
    expect($basicTee->densities->pluck('name')->sort()->values()->all())->toBe(['140-150 гр', '160 гр', '180 гр']);
    expect($basicTee->priceTiers)->toHaveCount(3);
});

test('the merge migration is a no-op when the survivor slug does not exist', function () {
    $migration = include database_path('migrations/2026_09_14_000001_merge_basic_and_oversize_tee_density_variants.php');

    $migration->up();

    expect(Product::query()->count())->toBe(0);
    expect(Redirect::query()->count())->toBe(0);
});
