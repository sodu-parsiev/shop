<?php

use App\Models\Content\Redirect;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * These three products' slugs were left over from before their density
     * labels were corrected, so the URL no longer matches the density shown
     * on the card/page (e.g. kids-tee-175-185 displays "180 гр").
     *
     * @var array<string, string>
     */
    private const SKU_TO_NEW_SLUG = [
        'SH-KIDS-TEE-180' => 'kids-tee-180',
        'SH-LONG-145' => 'longsleeve-180',
        'SH-HOODIE-3T-270' => 'hoodie-three-thread-320',
    ];

    /**
     * @var array<string, string>
     */
    private const SKU_TO_OLD_SLUG = [
        'SH-KIDS-TEE-180' => 'kids-tee-175-185',
        'SH-LONG-145' => 'longsleeve-140-150',
        'SH-HOODIE-3T-270' => 'hoodie-three-thread-260-280',
    ];

    /**
     * Old slug => new slug, for 301 redirects. Includes the retired hoodie
     * slug that already redirected to the old (now also retired) hoodie slug.
     *
     * @var array<string, string>
     */
    private const OLD_SLUG_REDIRECTS = [
        'kids-tee-175-185' => 'kids-tee-180',
        'longsleeve-140-150' => 'longsleeve-180',
        'hoodie-three-thread-260-280' => 'hoodie-three-thread-320',
        'hoodie-two-thread-220-240' => 'hoodie-three-thread-320',
    ];

    /**
     * No-op on a fresh install: products don't exist at migration time (the
     * seeders produce the final, already-correct slugs directly). Only
     * matters for an existing production database carrying the mismatched
     * slugs from before the density correction.
     */
    public function up(): void
    {
        $anyTargetExists = DB::table('products')->whereIn('sku', array_keys(self::SKU_TO_NEW_SLUG))->exists();

        if (! $anyTargetExists) {
            return;
        }

        foreach (self::SKU_TO_NEW_SLUG as $sku => $newSlug) {
            DB::table('products')->where('sku', $sku)->update(['slug' => $newSlug]);
        }

        foreach (self::OLD_SLUG_REDIRECTS as $oldSlug => $newSlug) {
            Redirect::query()->updateOrCreate(
                ['source_path' => "/catalog/{$oldSlug}"],
                ['target_url' => "/catalog/{$newSlug}", 'status_code' => 301, 'is_active' => true],
            );
        }
    }

    public function down(): void
    {
        foreach (self::SKU_TO_OLD_SLUG as $sku => $oldSlug) {
            DB::table('products')->where('sku', $sku)->update(['slug' => $oldSlug]);
        }
    }
};
