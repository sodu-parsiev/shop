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
        ->count())->toBe(14);

    $product = Product::query()
        ->with(['densities', 'priceTiers'])
        ->where('slug', 'basic-tee-140-150')
        ->firstOrFail();

    expect($product->name)->toBe('Базовая футболка 140-150 гр');
    expect($product->moq)->toBe(10);
    expect($product->densities->pluck('name')->all())->toBe(['140-150 гр']);
    expect($product->priceTiers)->toHaveCount(6);

    $this->assertDatabaseHas('product_price_tiers', [
        'product_id' => $product->id,
        'quantity' => 5000,
        'unit_price' => 170,
        'currency' => 'RUB',
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
