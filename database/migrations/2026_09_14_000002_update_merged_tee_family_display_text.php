<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The prior merge migration (2026_09_14_000001) consolidated price tiers,
     * densities, and retirement/redirects for the two survivor products, but
     * missed updating their own display text (name/h1/description/meta) to the
     * merged-product wording CatalogSeeder now produces. This fixes that gap.
     *
     * No-op on a fresh install (CatalogSeeder already seeds the correct text).
     */
    private const UPDATES = [
        'basic-tee-140-150' => [
            'name' => 'Базовая футболка',
            'h1' => 'Базовая футболка оптом',
            'short_description' => 'Базовая футболка: бланковый текстиль, плотности 140-150 гр, 160 гр, 180 гр.',
            'description' => 'Базовая футболка: бланковый текстиль, плотности 140-150 гр, 160 гр, 180 гр. Цена «на заказ» актуальна для изменения цвета изделия или вшивных ярлыков; изменения фасона, ткани, фурнитуры и материалов для ярлыков рассчитывает менеджер.',
            'meta_title' => 'Базовая футболка — бланковый текстиль оптом',
            'meta_description' => 'Базовая футболка: бланковый текстиль, плотности 140-150 гр, 160 гр, 180 гр. Цены указаны за бланковый текстиль.',
        ],
        'oversize-tee-180' => [
            'name' => 'Оверсайз футболка',
            'h1' => 'Оверсайз футболка оптом',
            'short_description' => 'Оверсайз футболка: бланковый текстиль, плотности 180 гр, 220 гр.',
            'description' => 'Оверсайз футболка: бланковый текстиль, плотности 180 гр, 220 гр. Цена «на заказ» актуальна для изменения цвета изделия или вшивных ярлыков; изменения фасона, ткани, фурнитуры и материалов для ярлыков рассчитывает менеджер.',
            'meta_title' => 'Оверсайз футболка — бланковый текстиль оптом',
            'meta_description' => 'Оверсайз футболка: бланковый текстиль, плотности 180 гр, 220 гр. Цены указаны за бланковый текстиль.',
        ],
    ];

    /**
     * @var array<string, array<string, string>>
     */
    private const OLD_NAMES = [
        'basic-tee-140-150' => 'Базовая футболка 140-150 гр',
        'oversize-tee-180' => 'Оверсайз футболка 180 гр',
    ];

    public function up(): void
    {
        foreach (self::UPDATES as $slug => $fields) {
            DB::table('products')->where('slug', $slug)->update($fields + ['updated_at' => now()]);
        }
    }

    public function down(): void
    {
        foreach (self::OLD_NAMES as $slug => $name) {
            DB::table('products')->where('slug', $slug)->update([
                'name' => $name,
                'h1' => $name.' оптом',
                'updated_at' => now(),
            ]);
        }
    }
};
