<?php

use App\Enums\ProductStatus;
use App\Models\Catalog\Density;
use App\Models\Catalog\Product;
use App\Models\Content\Redirect;
use Database\Seeders\CatalogRedirectSeeder;
use Database\Seeders\CatalogSeeder;

test('catalog seeder creates the real price list products with tiers and density axes', function () {
    $this->seed(CatalogSeeder::class);

    expect(Product::query()
        ->where('status', ProductStatus::Active)
        ->where('show_on_landing', true)
        ->count())->toBe(7);

    $product = Product::query()
        ->with(['densities', 'priceTiers'])
        ->where('slug', 'basic-tee-140-150')
        ->firstOrFail();

    expect($product->name)->toBe('Базовая футболка');
    expect($product->moq)->toBe(100);
    expect($product->densities->pluck('name')->all())->toBe(['140-150 гр', '160 гр', '180 гр']);
    expect($product->priceTiers)->toHaveCount(12);
    expect($product->isDensityPriced())->toBeTrue();

    $density140 = Density::query()->where('name', '140-150 гр')->firstOrFail();

    $this->assertDatabaseHas('product_price_tiers', [
        'product_id' => $product->id,
        'density_id' => $density140->id,
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
        'kids-tee-175-185' => ['180 гр'],
        'longsleeve-140-150' => ['180 гр'],
        'hoodie-three-thread-260-280' => ['320 гр'],
    ];

    foreach ($densityBySlug as $slug => $expectedDensities) {
        $product = Product::query()->with('densities')->where('slug', $slug)->firstOrFail();

        expect($product->densities->pluck('name')->all())->toBe($expectedDensities);
        expect($product->isDensityPriced())->toBeFalse();
    }

    expect(Density::query()->pluck('name')->sort()->values()->all())
        ->toBe(['140-150 гр', '160 гр', '180 гр', '220 гр', '320 гр']);

    expect(Product::query()->where('slug', 'oversize-tee-200-210')->exists())->toBeFalse();
});

test('catalog seeder trims the catalog to the final assortment', function () {
    $this->seed(CatalogSeeder::class);

    foreach (['women-tee-180', 'sweatshirt-two-thread-220-240', 'hoodie-two-thread-220-240'] as $slug) {
        expect(Product::query()->where('slug', $slug)->exists())->toBeFalse();
    }

    $hoodie = Product::query()->with(['colors', 'priceTiers'])->where('slug', 'hoodie-three-thread-260-280')->firstOrFail();
    expect($hoodie->name)->toBe('Худи');
    expect($hoodie->colors->pluck('name')->all())->toBe(['Чёрный']);
    expect($hoodie->priceTierForQuantity(100)->unit_price)->toBe('11.94');

    $basicTee = Product::query()->with('colors')->where('slug', 'basic-tee-140-150')->firstOrFail();
    expect($basicTee->colors->pluck('name')->all())->toBe(['Белый', 'Чёрный']);
    expect($basicTee->colors->pluck('hex_code')->all())->toBe(['#FFFFFF', '#000000']);

    foreach (['oversize-tee-180', 'kids-tee-175-185', 'longsleeve-140-150'] as $slug) {
        $product = Product::query()->with('colors')->where('slug', $slug)->firstOrFail();

        expect($product->colors->pluck('name')->all())->toBe(['Белый', 'Чёрный']);
    }

    expect(Product::query()->where('slug', 'kids-tee-175-185')->value('name'))->toBe('Детская футболка');
    expect(Product::query()->where('slug', 'longsleeve-140-150')->value('name'))->toBe('Лонгслив');
    expect(Product::query()->where('name', 'like', '%гр%')->count())->toBe(0);

    foreach (['baseball-cap', 'shopper'] as $slug) {
        expect(Product::query()->with('colors')->where('slug', $slug)->firstOrFail()->colors)->toHaveCount(0);
    }
});

test('catalog seeder merges density-only tee families into single variant products', function () {
    $this->seed([CatalogSeeder::class, CatalogRedirectSeeder::class]);

    foreach (['basic-tee-155-165', 'basic-tee-175-185', 'oversize-tee-220-240'] as $slug) {
        expect(Product::query()->where('slug', $slug)->exists())->toBeFalse();
    }

    $basicTee = Product::query()->with(['densities', 'priceTiers'])->where('slug', 'basic-tee-140-150')->firstOrFail();
    expect($basicTee->densities->pluck('name')->all())->toBe(['140-150 гр', '160 гр', '180 гр']);
    expect($basicTee->priceTierForQuantity(1000, Density::where('name', '140-150 гр')->value('id'))->unit_price)->toBe('2.19');
    expect($basicTee->priceTierForQuantity(1000, Density::where('name', '160 гр')->value('id'))->unit_price)->toBe('2.44');
    expect($basicTee->priceTierForQuantity(1000, Density::where('name', '180 гр')->value('id'))->unit_price)->toBe('2.69');

    $oversizeTee = Product::query()->with(['densities', 'priceTiers'])->where('slug', 'oversize-tee-180')->firstOrFail();
    expect($oversizeTee->densities->pluck('name')->all())->toBe(['180 гр', '220 гр']);
    expect($oversizeTee->priceTierForQuantity(100, Density::where('name', '180 гр')->value('id'))->unit_price)->toBe('3.56');
    expect($oversizeTee->priceTierForQuantity(100, Density::where('name', '220 гр')->value('id'))->unit_price)->toBe('4.19');

    expect(Redirect::where('source_path', '/catalog/basic-tee-155-165')->value('target_url'))->toBe('/catalog/basic-tee-140-150');
    expect(Redirect::where('source_path', '/catalog/basic-tee-175-185')->value('target_url'))->toBe('/catalog/basic-tee-140-150');
    expect(Redirect::where('source_path', '/catalog/oversize-tee-220-240')->value('target_url'))->toBe('/catalog/oversize-tee-180');
    expect(Redirect::where('source_path', '/catalog/hoodie-two-thread-220-240')->value('target_url'))->toBe('/catalog/hoodie-three-thread-260-280');
    expect(Redirect::where('source_path', '/catalog/women-tee-180')->value('target_url'))->toBe('/#catalog');
    expect(Redirect::where('source_path', '/catalog/sweatshirt-two-thread-220-240')->value('target_url'))->toBe('/#catalog');
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
