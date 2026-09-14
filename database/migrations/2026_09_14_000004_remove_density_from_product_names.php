<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Product names must not carry the density — that's spec-row data, not a
     * title ("Детские 180 гр" → "Детская футболка"). Strings copied verbatim
     * from what CatalogSeeder now derives, matching the established pattern.
     *
     * @var array<string, array<string, string>>
     */
    private const UPDATES = [
        'kids-tee-175-185' => [
            'name' => 'Детская футболка',
            'h1' => 'Детская футболка оптом',
            'short_description' => 'Детская футболка: бланковый текстиль, плотность 180 гр.',
            'description' => 'Детская футболка: бланковый текстиль, плотность 180 гр. Цена «на заказ» актуальна для изменения цвета изделия или вшивных ярлыков; изменения фасона, ткани, фурнитуры и материалов для ярлыков рассчитывает менеджер.',
            'meta_title' => 'Детская футболка — бланковый текстиль оптом',
            'meta_description' => 'Детская футболка: бланковый текстиль, плотность 180 гр. Цены указаны за бланковый текстиль.',
        ],
        'longsleeve-140-150' => [
            'name' => 'Лонгслив',
            'h1' => 'Лонгслив оптом',
            'short_description' => 'Лонгслив: бланковый текстиль, плотность 180 гр.',
            'description' => 'Лонгслив: бланковый текстиль, плотность 180 гр. Цена «на заказ» актуальна для изменения цвета изделия или вшивных ярлыков; изменения фасона, ткани, фурнитуры и материалов для ярлыков рассчитывает менеджер.',
            'meta_title' => 'Лонгслив — бланковый текстиль оптом',
            'meta_description' => 'Лонгслив: бланковый текстиль, плотность 180 гр. Цены указаны за бланковый текстиль.',
        ],
    ];

    /**
     * @var array<string, string>
     */
    private const OLD_NAMES = [
        'kids-tee-175-185' => 'Детские 180 гр',
        'longsleeve-140-150' => 'Лонгслив 180 гр',
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
