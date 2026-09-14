<?php

use App\Enums\ProductStatus;
use App\Models\Catalog\Density;
use App\Models\Catalog\Product;
use Database\Seeders\CatalogSeeder;

test('catalog seeder creates the real price list products with tiers and density axes', function () {
    $this->seed(CatalogSeeder::class);

    expect(Product::query()
        ->where('status', ProductStatus::Active)
        ->where('show_on_landing', true)
        ->count())->toBe(13);

    $product = Product::query()
        ->with(['densities', 'priceTiers'])
        ->where('slug', 'basic-tee-140-150')
        ->firstOrFail();

    expect($product->name)->toBe('Базовая футболка 140-150 гр');
    expect($product->moq)->toBe(100);
    expect($product->densities->pluck('name')->all())->toBe(['140-150 гр']);
    expect($product->priceTiers)->toHaveCount(4);

    $this->assertDatabaseHas('product_price_tiers', [
        'product_id' => $product->id,
        'quantity' => 5000,
        'unit_price' => 2.13,
        'currency' => 'USD',
    ]);

    expect(Density::query()->where('name', '140-150 гр')->exists())->toBeTrue();
});

test('catalog seeder publishes the shopper without price tiers', function () {
    $this->seed(CatalogSeeder::class);

    $shopper = Product::query()
        ->with('priceTiers')
        ->where('slug', 'shopper')
        ->firstOrFail();

    expect($shopper->show_on_landing)->toBeTrue();
    expect($shopper->startingPriceLabel())->toBe('По запросу');
    expect($shopper->priceTiers)->toHaveCount(0);
});

test('catalog seeder populates the basic tee size chart and size picker', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::query()
        ->with('sizes')
        ->where('slug', 'basic-tee-140-150')
        ->firstOrFail();

    expect($product->size_table)->toHaveCount(14);
    expect($product->sizes)->toHaveCount(14);

    $mRow = collect($product->size_table)->firstWhere('size', 'M (48)');
    expect($mRow)->toBe([
        'size' => 'M (48)',
        'chest' => '53',
        'length' => '72.5',
        'sleeve' => '22.5',
    ]);
});

test('catalog seeder consolidates densities per the assortment update', function () {
    $this->seed(CatalogSeeder::class);

    $densityBySlug = [
        'basic-tee-155-165' => ['160 гр'],
        'basic-tee-175-185' => ['180 гр'],
        'kids-tee-175-185' => ['180 гр'],
        'oversize-tee-220-240' => ['220 гр'],
        'longsleeve-140-150' => ['180 гр'],
        'hoodie-two-thread-220-240' => ['320 гр'],
        'hoodie-three-thread-260-280' => ['320 гр'],
    ];

    foreach ($densityBySlug as $slug => $expectedDensities) {
        $product = Product::query()->with('densities')->where('slug', $slug)->firstOrFail();

        expect($product->densities->pluck('name')->all())->toBe($expectedDensities);
    }

    expect(Density::query()->pluck('name')->sort()->values()->all())
        ->toBe(['140-150 гр', '160 гр', '180 гр', '220 гр', '220-240 гр', '320 гр']);

    expect(Product::query()->where('slug', 'oversize-tee-200-210')->exists())->toBeFalse();
});

test('catalog seeder offers kids size 170 without a matching measurement row', function () {
    $this->seed(CatalogSeeder::class);

    $product = Product::query()
        ->with('sizes')
        ->where('slug', 'kids-tee-175-185')
        ->firstOrFail();

    expect($product->sizes->pluck('name')->all())->toContain('170');
    expect($product->sizes)->toHaveCount(9);
    expect($product->size_table)->toHaveCount(8);
    expect(collect($product->size_table)->pluck('size')->all())->not->toContain('170');
});
