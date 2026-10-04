<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * A photo per color each product is sold in. Without a tagged photo,
     * picking that color on the storefront keeps showing the cover
     * (e.g. the white tee when "Чёрный" is selected).
     *
     * @var array<string, array<string, string>>
     */
    private const SKU_COLOR_PHOTOS = [
        'SH-TEE-145' => [
            'Белый' => '/brand/products/basic-tee-140-150.jpg',
            'Чёрный' => '/brand/products/basic-tee-175-185.jpg',
        ],
        'SH-TEE-OVR-180' => [
            'Белый' => '/brand/products/oversize-tee-180-white.jpg',
            'Чёрный' => '/brand/products/oversize-tee-180-black.jpg',
        ],
        'SH-KIDS-TEE-180' => [
            'Белый' => '/brand/products/kids-tee-175-185.jpg',
            'Чёрный' => '/brand/products/kids-tee-180-black.jpg',
        ],
        'SH-LONG-145' => [
            'Белый' => '/brand/products/longsleeve-180-white.jpg',
            'Чёрный' => '/brand/products/longsleeve-180-black.jpg',
        ],
        'SH-HOODIE-3T-270' => [
            'Чёрный' => '/brand/products/hoodie-320-black.jpg',
        ],
    ];

    /**
     * These covers showed a color the product isn't sold in (beige,
     * burgundy). SKU => [seeded cover, cover in an offered color].
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const SKU_COVER_SWAPS = [
        'SH-TEE-OVR-180' => ['/brand/products/oversize-tee-180.jpg', '/brand/products/oversize-tee-180-white.jpg'],
        'SH-LONG-145' => ['/brand/products/longsleeve-140-150.jpg', '/brand/products/longsleeve-180-white.jpg'],
        'SH-HOODIE-3T-270' => ['/brand/products/hoodie-three-thread-260-280.jpg', '/brand/products/hoodie-320-black.jpg'],
    ];

    /**
     * No-op on a fresh install (products are seeded later, CatalogSeeder sets
     * the same photos). Never overrides a color photo or cover an admin set.
     */
    public function up(): void
    {
        foreach ($this->photoRows() as $row) {
            $alreadyTagged = DB::table('product_images')
                ->where('product_id', $row['product_id'])
                ->where('color_id', $row['color_id'])
                ->exists();

            if ($alreadyTagged) {
                continue;
            }

            DB::table('product_images')->insert([
                ...$row,
                'sort_order' => (int) DB::table('product_images')->where('product_id', $row['product_id'])->max('sort_order') + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach (self::SKU_COVER_SWAPS as $sku => [$seededCover, $colorCover]) {
            DB::table('products')
                ->where('sku', $sku)
                ->where('cover_image', $seededCover)
                ->update(['cover_image' => $colorCover]);
        }
    }

    public function down(): void
    {
        foreach (self::SKU_COVER_SWAPS as $sku => [$seededCover, $colorCover]) {
            DB::table('products')
                ->where('sku', $sku)
                ->where('cover_image', $colorCover)
                ->update(['cover_image' => $seededCover]);
        }

        foreach ($this->photoRows() as $row) {
            DB::table('product_images')
                ->where('product_id', $row['product_id'])
                ->where('color_id', $row['color_id'])
                ->where('path', $row['path'])
                ->delete();
        }
    }

    /**
     * @return array<int, array{product_id: int, color_id: int, path: string, alt_text: string}>
     */
    private function photoRows(): array
    {
        $rows = [];

        foreach (self::SKU_COLOR_PHOTOS as $sku => $photos) {
            $product = DB::table('products')->where('sku', $sku)->first(['id', 'name']);

            if (! $product) {
                continue;
            }

            foreach ($photos as $colorName => $path) {
                $colorId = DB::table('colors')->where('name', $colorName)->value('id');

                if (! $colorId) {
                    continue;
                }

                $rows[] = [
                    'product_id' => $product->id,
                    'color_id' => $colorId,
                    'path' => $path,
                    'alt_text' => $product->name.' — '.mb_strtolower($colorName),
                ];
            }
        }

        return $rows;
    }
};
