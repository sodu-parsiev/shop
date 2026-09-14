<?php

use App\Enums\ProductStatus;
use App\Models\Catalog\Category;
use App\Models\Catalog\Color;
use App\Models\Catalog\Density;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductPriceTier;
use App\Models\Content\HomePageContent;
use App\Models\Content\Redirect;
use Illuminate\Support\Facades\DB;

/**
 * Simulates the pre-trim production shape: the post-merge 10-product catalog
 * (only the products this migration touches are seeded), colorless, with the
 * old hoodie naming and the old count label.
 */
function seedPreTrimCatalog(): void
{
    $category = Category::factory()->create(['name' => 'Худи']);
    $density320 = Density::factory()->create(['name' => '320 гр', 'gsm' => 320]);

    $rows = [
        ['women-tee-180', 'Женские 180 гр'],
        ['sweatshirt-two-thread-220-240', 'Свитшот 2х нитка 220-240 гр'],
        ['hoodie-two-thread-220-240', 'Худи 2х нитка 320 гр'],
        ['hoodie-three-thread-260-280', 'Худи 3х нитка 320 гр'],
        ['basic-tee-140-150', 'Базовая футболка'],
        ['longsleeve-140-150', 'Лонгслив 180 гр'],
    ];

    foreach ($rows as [$slug, $name]) {
        $product = Product::factory()->create([
            'slug' => $slug,
            'name' => $name,
            'category_id' => $category->id,
            'status' => ProductStatus::Active,
            'show_on_landing' => true,
        ]);
        $product->densities()->attach($density320->id);

        ProductPriceTier::factory()->create([
            'product_id' => $product->id,
            'quantity' => 100,
            'unit_price' => 5.00,
        ]);
    }

    HomePageContent::query()->create(['content' => ['catalog' => ['count_label' => '14 моделей', 'heading' => 'Бланковый текстиль']]]);
}

test('the trim migration retires off-assortment products and finalizes the hoodie', function () {
    seedPreTrimCatalog();

    $migration = include database_path('migrations/2026_09_14_000003_trim_catalog_to_final_assortment.php');
    $migration->up();

    foreach (['women-tee-180', 'sweatshirt-two-thread-220-240', 'hoodie-two-thread-220-240'] as $slug) {
        $retired = Product::query()->with('priceTiers')->where('slug', $slug)->firstOrFail();
        expect($retired->status)->toBe(ProductStatus::Inactive);
        expect($retired->show_on_landing)->toBeFalse();
        expect($retired->priceTiers)->toHaveCount(0);
        expect(DB::table('product_density')->where('product_id', $retired->id)->exists())->toBeFalse();
    }

    expect(Redirect::where('source_path', '/catalog/women-tee-180')->value('target_url'))->toBe('/#catalog');
    expect(Redirect::where('source_path', '/catalog/sweatshirt-two-thread-220-240')->value('target_url'))->toBe('/#catalog');
    expect(Redirect::where('source_path', '/catalog/hoodie-two-thread-220-240')->value('target_url'))->toBe('/catalog/hoodie-three-thread-260-280');

    $hoodie = Product::query()->with('colors')->where('slug', 'hoodie-three-thread-260-280')->firstOrFail();
    expect($hoodie->name)->toBe('Худи');
    expect($hoodie->h1)->toBe('Худи оптом');
    expect($hoodie->meta_title)->toBe('Худи — бланковый текстиль оптом');
    expect($hoodie->short_description)->toBe('Худи: бланковый текстиль, плотность 320 гр.');
    expect($hoodie->colors->pluck('name')->all())->toBe(['Чёрный']);

    $basicTee = Product::query()->with('colors')->where('slug', 'basic-tee-140-150')->firstOrFail();
    expect($basicTee->colors->pluck('name')->all())->toBe(['Белый', 'Чёрный']);

    $longsleeve = Product::query()->with('colors')->where('slug', 'longsleeve-140-150')->firstOrFail();
    expect($longsleeve->colors->pluck('name')->all())->toBe(['Белый', 'Чёрный']);

    expect(Color::where('name', 'Белый')->value('hex_code'))->toBe('#FFFFFF');
    expect(Color::where('name', 'Чёрный')->value('hex_code'))->toBe('#000000');

    $content = HomePageContent::query()->firstOrFail();
    expect($content->get('catalog.count_label'))->toBe('7 моделей');
    expect($content->get('catalog.heading'))->toBe('Бланковый текстиль');
});

test('the trim migration is idempotent', function () {
    seedPreTrimCatalog();

    $migration = include database_path('migrations/2026_09_14_000003_trim_catalog_to_final_assortment.php');
    $migration->up();
    $migration->up();

    $hoodie = Product::query()->with('colors')->where('slug', 'hoodie-three-thread-260-280')->firstOrFail();
    expect($hoodie->name)->toBe('Худи');
    expect($hoodie->colors)->toHaveCount(1);

    expect(Color::where('name', 'Чёрный')->count())->toBe(1);
    expect(Redirect::where('source_path', '/catalog/women-tee-180')->count())->toBe(1);
});

test('the trim migration is a no-op on a fresh install', function () {
    $migration = include database_path('migrations/2026_09_14_000003_trim_catalog_to_final_assortment.php');

    $migration->up();

    expect(Product::query()->count())->toBe(0);
    expect(Redirect::query()->count())->toBe(0);
    expect(Color::query()->count())->toBe(0);
});
