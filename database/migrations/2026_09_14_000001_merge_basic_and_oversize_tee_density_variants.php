<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * @var array<string, array<int, string>>
     */
    private const FAMILIES = [
        'basic-tee-140-150' => ['basic-tee-155-165', 'basic-tee-175-185'],
        'oversize-tee-180' => ['oversize-tee-220-240'],
    ];

    /**
     * Run the migrations.
     *
     * On a fresh install this is a no-op — the products table is still empty at
     * migration time (seeders run after all migrations), and CatalogSeeder already
     * creates the merged shape directly. This migration only matters for an existing
     * production database where the 5 pre-merge product rows already exist.
     */
    public function up(): void
    {
        foreach (self::FAMILIES as $survivorSlug => $retiredSlugs) {
            $this->mergeFamily($survivorSlug, $retiredSlugs);
        }
    }

    /**
     * Reverse the migrations.
     *
     * Best-effort only: un-hides the retired products and removes their redirects.
     * The price-tier merge itself is not losslessly reversible (the retired products'
     * own price tier rows are deleted, not archived) — this mirrors how the
     * 2026_09_02_000000 currency-conversion migration treats its own one-way change.
     */
    public function down(): void
    {
        foreach (self::FAMILIES as $survivorSlug => $retiredSlugs) {
            foreach ($retiredSlugs as $slug) {
                DB::table('products')->where('slug', $slug)->update([
                    'status' => 'active',
                    'show_on_landing' => true,
                    'updated_at' => now(),
                ]);
                DB::table('redirects')->where('source_path', "/catalog/{$slug}")->delete();
            }
        }
    }

    /**
     * @param  array<int, string>  $retiredSlugs
     */
    private function mergeFamily(string $survivorSlug, array $retiredSlugs): void
    {
        $survivorId = DB::table('products')->where('slug', $survivorSlug)->value('id');

        if (! $survivorId) {
            return;
        }

        $survivorDensityId = DB::table('product_density')->where('product_id', $survivorId)->value('density_id');

        if ($survivorDensityId) {
            DB::table('product_price_tiers')
                ->where('product_id', $survivorId)
                ->whereNull('density_id')
                ->update(['density_id' => $survivorDensityId, 'updated_at' => now()]);
        }

        $blockOffset = 10;

        foreach ($retiredSlugs as $slug) {
            $retiredId = DB::table('products')->where('slug', $slug)->value('id');

            if (! $retiredId) {
                $blockOffset += 10;

                continue;
            }

            $densityId = DB::table('product_density')->where('product_id', $retiredId)->value('density_id');

            foreach (DB::table('product_price_tiers')->where('product_id', $retiredId)->get() as $tier) {
                DB::table('product_price_tiers')->updateOrInsert(
                    [
                        'product_id' => $survivorId,
                        'density_id' => $densityId,
                        'quantity' => $tier->quantity,
                        'currency' => $tier->currency,
                    ],
                    [
                        'unit_price' => $tier->unit_price,
                        'sort_order' => $blockOffset + $tier->sort_order,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ],
                );
            }

            if ($densityId) {
                DB::table('product_density')->updateOrInsert(
                    ['product_id' => $survivorId, 'density_id' => $densityId],
                    [],
                );
            }

            DB::table('product_price_tiers')->where('product_id', $retiredId)->delete();
            DB::table('product_density')->where('product_id', $retiredId)->delete();

            DB::table('products')->where('id', $retiredId)->update([
                'status' => 'inactive',
                'show_on_landing' => false,
                'updated_at' => now(),
            ]);

            DB::table('redirects')->updateOrInsert(
                ['source_path' => "/catalog/{$slug}"],
                [
                    'target_url' => "/catalog/{$survivorSlug}",
                    'status_code' => 301,
                    'is_active' => true,
                    'hits' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );

            $blockOffset += 10;
        }
    }
};
