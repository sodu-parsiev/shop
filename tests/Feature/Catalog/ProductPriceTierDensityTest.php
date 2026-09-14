<?php

use App\Models\Catalog\Density;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductPriceTier;
use App\Services\Currency\CentralBankCurrencyRateService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::put(CentralBankCurrencyRateService::USD_RUB_CACHE_KEY, 80, now()->addDay());
});

test('a non-variant product is not density priced', function () {
    $product = Product::factory()->create(['moq' => 100]);
    ProductPriceTier::factory()->create([
        'product_id' => $product->id,
        'density_id' => null,
        'quantity' => 500,
        'unit_price' => 3.5,
    ]);

    expect($product->isDensityPriced())->toBeFalse();
    expect($product->cheapestDensityId())->toBeNull();
});

test('a product with any density-tagged tier is density priced', function () {
    $product = Product::factory()->create(['moq' => 100]);
    $density = Density::factory()->create();
    ProductPriceTier::factory()->create([
        'product_id' => $product->id,
        'density_id' => $density->id,
        'quantity' => 500,
        'unit_price' => 3.5,
    ]);

    expect($product->isDensityPriced())->toBeTrue();
});

test('priceTierForQuantity resolves the correct tier per density', function () {
    $product = Product::factory()->create(['moq' => 100]);
    $light = Density::factory()->create(['name' => 'Light']);
    $heavy = Density::factory()->create(['name' => 'Heavy']);

    ProductPriceTier::factory()->create(['product_id' => $product->id, 'density_id' => $light->id, 'quantity' => 500, 'unit_price' => 2.00]);
    ProductPriceTier::factory()->create(['product_id' => $product->id, 'density_id' => $heavy->id, 'quantity' => 500, 'unit_price' => 2.50]);

    expect($product->priceTierForQuantity(500, $light->id)->unit_price)->toBe('2.00');
    expect($product->priceTierForQuantity(500, $heavy->id)->unit_price)->toBe('2.50');
});

test('priceTierForQuantity refuses to resolve without a density on a variant product', function () {
    $product = Product::factory()->create(['moq' => 100]);
    $density = Density::factory()->create();
    ProductPriceTier::factory()->create(['product_id' => $product->id, 'density_id' => $density->id, 'quantity' => 500, 'unit_price' => 2.00]);

    expect($product->priceTierForQuantity(500))->toBeNull();
});

test('cheapestDensityId and lowestPriceTier pick the globally cheapest variant', function () {
    $product = Product::factory()->create(['moq' => 100]);
    $light = Density::factory()->create(['name' => 'Light']);
    $heavy = Density::factory()->create(['name' => 'Heavy']);

    ProductPriceTier::factory()->create(['product_id' => $product->id, 'density_id' => $light->id, 'quantity' => 500, 'unit_price' => 2.00]);
    ProductPriceTier::factory()->create(['product_id' => $product->id, 'density_id' => $heavy->id, 'quantity' => 500, 'unit_price' => 2.50]);

    expect($product->cheapestDensityId())->toBe($light->id);
    expect($product->lowestPriceTier()->density_id)->toBe($light->id);
});

test('formattedPriceTiersByQuantity and availableOrderQuantities default to the cheapest density and dedupe', function () {
    $product = Product::factory()->create(['moq' => 100]);
    $light = Density::factory()->create(['name' => 'Light']);
    $heavy = Density::factory()->create(['name' => 'Heavy']);

    ProductPriceTier::factory()->create(['product_id' => $product->id, 'density_id' => $light->id, 'quantity' => 500, 'unit_price' => 2.00]);
    ProductPriceTier::factory()->create(['product_id' => $product->id, 'density_id' => $light->id, 'quantity' => 1000, 'unit_price' => 1.90]);
    ProductPriceTier::factory()->create(['product_id' => $product->id, 'density_id' => $heavy->id, 'quantity' => 500, 'unit_price' => 2.50]);
    ProductPriceTier::factory()->create(['product_id' => $product->id, 'density_id' => $heavy->id, 'quantity' => 1000, 'unit_price' => 2.40]);

    $quantities = $product->availableOrderQuantities();

    expect($quantities)->toBe(array_values(array_unique($quantities)));
    expect($quantities)->toContain(500, 1000);
    expect($product->formattedPriceTiersByQuantity($heavy->id))->toHaveKey('500');
});

test('priceTiersByDensity groups formatted prices by density id', function () {
    $product = Product::factory()->create(['moq' => 100]);
    $light = Density::factory()->create(['name' => 'Light']);
    $heavy = Density::factory()->create(['name' => 'Heavy']);

    ProductPriceTier::factory()->create(['product_id' => $product->id, 'density_id' => $light->id, 'quantity' => 500, 'unit_price' => 2.00]);
    ProductPriceTier::factory()->create(['product_id' => $product->id, 'density_id' => $heavy->id, 'quantity' => 500, 'unit_price' => 2.50]);

    $byDensity = $product->priceTiersByDensity();

    expect($byDensity)->toHaveKeys([$light->id, $heavy->id]);
    expect($byDensity[$light->id])->toHaveKey('500');
});

test('non-variant products keep identical default-arg pricing behavior', function () {
    $product = Product::factory()->create(['moq' => 100]);
    ProductPriceTier::factory()->create(['product_id' => $product->id, 'density_id' => null, 'quantity' => 500, 'unit_price' => 3.00]);
    ProductPriceTier::factory()->create(['product_id' => $product->id, 'density_id' => null, 'quantity' => 1000, 'unit_price' => 2.90]);

    expect($product->priceTierForQuantity(500)->unit_price)->toBe('3.00');
    expect($product->availableOrderQuantities())->toBe([500, 1000]);
    expect($product->formattedPriceTiersByQuantity())->toHaveKeys(['500', '1000']);
    expect($product->priceTiersByDensity())->toBe([]);
});
