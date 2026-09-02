<?php

use App\Models\Catalog\Product;
use Illuminate\Support\Facades\DB;

test('catalog price tier migration converts existing RUB prices to USD', function () {
    $product = Product::factory()->create();

    DB::table('product_price_tiers')->insert([
        'product_id' => $product->id,
        'quantity' => 5000,
        'unit_price' => 170,
        'currency' => 'RUB',
        'sort_order' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $migration = include database_path('migrations/2026_09_02_000000_store_product_price_tiers_in_usd.php');

    $migration->up();

    $tier = DB::table('product_price_tiers')
        ->where('product_id', $product->id)
        ->where('quantity', 5000)
        ->first();

    expect((float) $tier->unit_price)->toBe(2.13)
        ->and($tier->currency)->toBe('USD');
});
