<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Retired product slug => redirect target. Products absent from the final
     * assortment table redirect to the homepage catalog section; the retired
     * hoodie SKU redirects to the surviving hoodie.
     *
     * @var array<string, string>
     */
    private const RETIREMENTS = [
        'women-tee-180' => '/#catalog',
        'sweatshirt-two-thread-220-240' => '/#catalog',
        'hoodie-two-thread-220-240' => '/catalog/hoodie-three-thread-260-280',
    ];

    private const HOODIE_SLUG = 'hoodie-three-thread-260-280';

    /**
     * Color name => [hex, sort_order].
     *
     * @var array<string, array{string, int}>
     */
    private const COLORS = [
        'Белый' => ['#FFFFFF', 1],
        'Чёрный' => ['#000000', 2],
    ];

    /**
     * Product slug => color names per the final assortment table.
     *
     * @var array<string, array<int, string>>
     */
    private const PRODUCT_COLORS = [
        'basic-tee-140-150' => ['Белый', 'Чёрный'],
        'oversize-tee-180' => ['Белый', 'Чёрный'],
        'kids-tee-175-185' => ['Белый', 'Чёрный'],
        'longsleeve-140-150' => ['Белый', 'Чёрный'],
        'hoodie-three-thread-260-280' => ['Чёрный'],
    ];

    /**
     * Run the migrations.
     *
     * No-op on a fresh install (products don't exist at migration time; the
     * seeders produce the final state directly). Only matters for an existing
     * production database carrying the pre-trim 10-product catalog.
     */
    public function up(): void
    {
        $this->retireProducts();
        $this->renameHoodie();
        $this->attachColors();
        $this->patchCountLabel();
    }

    /**
     * Best-effort: un-hides the retired products and removes their redirects.
     * Their deleted price tiers / density pivots are not restored (mirrors the
     * one-way treatment in the 2026_09_14_000001 merge migration).
     */
    public function down(): void
    {
        foreach (array_keys(self::RETIREMENTS) as $slug) {
            DB::table('products')->where('slug', $slug)->update([
                'status' => 'active',
                'show_on_landing' => true,
                'updated_at' => now(),
            ]);
            DB::table('redirects')->where('source_path', "/catalog/{$slug}")->delete();
        }

        DB::table('products')->where('slug', self::HOODIE_SLUG)->update([
            'name' => 'Худи 3х нитка 320 гр',
            'h1' => 'Худи 3х нитка 320 гр оптом',
            'updated_at' => now(),
        ]);
    }

    private function retireProducts(): void
    {
        foreach (self::RETIREMENTS as $slug => $target) {
            $productId = DB::table('products')->where('slug', $slug)->value('id');

            if (! $productId) {
                continue;
            }

            DB::table('product_price_tiers')->where('product_id', $productId)->delete();
            DB::table('product_density')->where('product_id', $productId)->delete();

            DB::table('products')->where('id', $productId)->update([
                'status' => 'inactive',
                'show_on_landing' => false,
                'updated_at' => now(),
            ]);

            DB::table('redirects')->updateOrInsert(
                ['source_path' => "/catalog/{$slug}"],
                [
                    'target_url' => $target,
                    'status_code' => 301,
                    'is_active' => true,
                    'hits' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    private function renameHoodie(): void
    {
        DB::table('products')->where('slug', self::HOODIE_SLUG)->update([
            'name' => 'Худи',
            'h1' => 'Худи оптом',
            'short_description' => 'Худи: бланковый текстиль, плотность 320 гр.',
            'description' => 'Худи: бланковый текстиль, плотность 320 гр. Цена «на заказ» актуальна для изменения цвета изделия или вшивных ярлыков; изменения фасона, ткани, фурнитуры и материалов для ярлыков рассчитывает менеджер.',
            'meta_title' => 'Худи — бланковый текстиль оптом',
            'meta_description' => 'Худи: бланковый текстиль, плотность 320 гр. Цены указаны за бланковый текстиль.',
            'updated_at' => now(),
        ]);
    }

    private function attachColors(): void
    {
        $anyTargetExists = DB::table('products')
            ->whereIn('slug', array_keys(self::PRODUCT_COLORS))
            ->exists();

        if (! $anyTargetExists) {
            return;
        }

        $colorIds = [];

        foreach (self::COLORS as $name => [$hex, $sortOrder]) {
            DB::table('colors')->updateOrInsert(
                ['name' => $name],
                [
                    'hex_code' => $hex,
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );

            $colorIds[$name] = DB::table('colors')->where('name', $name)->value('id');
        }

        foreach (self::PRODUCT_COLORS as $slug => $colorNames) {
            $productId = DB::table('products')->where('slug', $slug)->value('id');

            if (! $productId) {
                continue;
            }

            foreach ($colorNames as $colorName) {
                DB::table('product_color')->updateOrInsert(
                    ['product_id' => $productId, 'color_id' => $colorIds[$colorName]],
                    [],
                );
            }
        }
    }

    private function patchCountLabel(): void
    {
        $row = DB::table('home_page_contents')->where('id', 1)->first();

        if (! $row) {
            return;
        }

        $content = json_decode($row->content, true);

        if (! is_array($content)) {
            return;
        }

        $content['catalog']['count_label'] = '7 моделей';

        DB::table('home_page_contents')->where('id', 1)->update([
            'content' => json_encode($content, JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);
    }
};
