<?php

namespace Database\Seeders;

use App\Enums\AvailabilityStatus;
use App\Enums\ProductStatus;
use App\Models\Catalog\Category;
use App\Models\Catalog\Color;
use App\Models\Catalog\CustomizationService;
use App\Models\Catalog\Density;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductPriceTier;
use App\Models\Catalog\Size;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class CatalogSeeder extends Seeder
{
    private const SOURCE_RUB_TO_USD_RATE = 80;

    /**
     * @var array<int, string>
     */
    private const LEGACY_PRODUCT_SLUGS = [
        'basic-tee-white',
        'basic-tee-black',
        'brand-color-tee',
        'heavy-oversize-tee',
        'full-cycle-custom-production',
        'oversize-tee-200-210',
        'basic-tee-155-165',
        'basic-tee-175-185',
        'oversize-tee-220-240',
    ];

    /**
     * Source: "Размеры.xlsx", "Футболки 140/160/180 гр." column.
     *
     * @var array<int, string>
     */
    private const TEE_SIZES = ['2XS', 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL', '6XL', '7XL', '8XL', '9XL'];

    /**
     * Source: "Размеры.xlsx", "Футболки Оверсайз 180/240 гр." column — a subset of TEE_SIZES.
     *
     * @var array<int, string>
     */
    private const OVERSIZE_SIZES = ['S', 'M', 'L', 'XL', '2XL', '3XL'];

    /**
     * Source: "Размеры.xlsx", "ДЕТСКИЕ футболки 180 гр." column.
     *
     * @var array<int, string>
     */
    private const KIDS_SIZES = ['122', '128', '134', '140', '146', '152', '158', '164', '170'];

    public function run(): void
    {
        $categories = $this->syncCategories();
        $densities = $this->syncDensities();
        $services = $this->syncCustomizationServices();

        $this->syncColors();
        $sizes = $this->syncSizes();
        $this->hideLegacyProducts();

        foreach ($this->productRows() as $sortOrder => $row) {
            $product = Product::query()->updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'h1' => $row['name'].' оптом',
                    'category_id' => $categories->get($row['category'])->id,
                    'sku' => $row['sku'],
                    'short_description' => $row['short_description'],
                    'description' => $row['description'],
                    'composition' => 'По спецификации партии',
                    'fit' => $row['fit'],
                    'size_table' => $row['size_table'],
                    'moq' => 100,
                    'stock_conditions' => $row['stock_conditions'],
                    'availability_status' => $row['availability_status'],
                    'stock_quantity' => $row['availability_status'] === AvailabilityStatus::InStock ? 1 : null,
                    'status' => ProductStatus::Active,
                    'featured' => $sortOrder < 6,
                    'show_on_landing' => true,
                    'sort_order' => $sortOrder,
                    'meta_title' => $row['name'].' — бланковый текстиль оптом',
                    'meta_description' => $row['short_description'].' Цены указаны за бланковый текстиль.',
                    'canonical_url' => null,
                    'og_image' => null,
                    'cover_image' => $row['cover_image'],
                ],
            );

            $product->customizationServices()->sync($services->pluck('id'));
            $product->colors()->sync([]);
            $this->syncProductSizes($product, $sizes, $row['size_names']);

            if (isset($row['densities'])) {
                $this->syncVariantDensitiesAndPrices($product, $densities, $row['densities']);
            } else {
                $product->densities()->sync(
                    $row['density'] ? [$densities->get($row['density'])->id] : []
                );
                $this->syncPriceTiers($product, $row['prices']);
            }
        }
    }

    /**
     * @param  array<int, array{density: string, prices: array<int, int>}>  $variants
     */
    private function syncVariantDensitiesAndPrices(Product $product, Collection $densities, array $variants): void
    {
        $densityIds = collect($variants)->map(fn (array $variant) => $densities->get($variant['density'])->id);
        $product->densities()->sync($densityIds);

        foreach ($variants as $blockIndex => $variant) {
            $this->syncPriceTiers($product, $variant['prices'], $densities->get($variant['density'])->id, $blockIndex * 10);
        }

        $product->priceTiers()
            ->where('currency', ProductPriceTier::DEFAULT_CURRENCY)
            ->whereNotIn('density_id', $densityIds->all())
            ->delete();
    }

    /**
     * @return Collection<string, Category>
     */
    private function syncCategories(): Collection
    {
        return collect([
            'Футболки',
            'Детская одежда',
            'Женская одежда',
            'Лонгсливы',
            'Свитшоты',
            'Худи',
            'Аксессуары',
        ])->mapWithKeys(function (string $name, int $sortOrder): array {
            $category = Category::query()->updateOrCreate(
                ['name' => $name],
                ['sort_order' => $sortOrder, 'is_active' => true],
            );

            return [$name => $category];
        });
    }

    /**
     * @return Collection<string, Density>
     */
    private function syncDensities(): Collection
    {
        return collect([
            '140-150 гр' => 145,
            '160 гр' => 160,
            '180 гр' => 180,
            '220 гр' => 230,
            '220-240 гр' => 230,
            '320 гр' => 320,
        ])->mapWithKeys(function (int $gsm, string $name): array {
            $density = Density::query()->updateOrCreate(
                ['name' => $name],
                [
                    'gsm' => $gsm,
                    'sort_order' => $gsm,
                    'is_active' => true,
                ],
            );

            return [$name => $density];
        });
    }

    private function syncColors(): void
    {
        Color::query()->firstOrCreate(
            ['name' => 'Цвет по ТЗ'],
            ['hex_code' => null, 'sort_order' => 0, 'is_active' => true],
        );
    }

    /**
     * @return Collection<string, Size>
     */
    private function syncSizes(): Collection
    {
        return collect([
            ...self::TEE_SIZES,
            ...self::KIDS_SIZES,
        ])
            ->values()
            ->mapWithKeys(function (string $name, int $sortOrder): array {
                $size = Size::query()->updateOrCreate(
                    ['name' => $name],
                    ['sort_order' => $sortOrder, 'is_active' => true],
                );

                return [$name => $size];
            });
    }

    /**
     * @param  array<int, string>  $sizeNames
     */
    private function syncProductSizes(Product $product, Collection $sizes, array $sizeNames): void
    {
        $product->sizes()->sync($sizes->only($sizeNames)->pluck('id'));
    }

    /**
     * Source: "Табель мер OMG классика.xlsx", sheet 2, rows A/E/S, trimmed to the
     * 2XS–9XL range actually sold per "Размеры.xlsx".
     *
     * @return array<int, array{size: string, chest: string, length: string, sleeve: string}>
     */
    private function basicTeeSizeTable(): array
    {
        return $this->zipSizeTable(
            ['2XS (42)', 'XS (44)', 'S (46)', 'M (48)', 'L (50)', 'XL (52)', '2XL (54)', '3XL (56)', '4XL (58)', '5XL (60)', '6XL (62)', '7XL (64)', '8XL (66)', '9XL (68)'],
            [47, 49, 51, 53, 55, 57.5, 60, 62.5, 65, 67.5, 70, 72, 74, 76],
            [68, 69.5, 71, 72.5, 74, 76, 78, 80, 82, 84, 86, 86.5, 87, 87.5],
            [21, 21.5, 22, 22.5, 23, 23.5, 24, 24.5, 25, 25.5, 26, 26.5, 27, 27.5],
        );
    }

    /**
     * Source: "Табельмер Оверсайза Новый.xlsx" (current revision, SS 26/04, model M4073),
     * rows A/D/P.
     *
     * @return array<int, array{size: string, chest: string, length: string, sleeve: string}>
     */
    private function oversizeSizeTable(): array
    {
        return $this->zipSizeTable(
            ['S (46)', 'M (48)', 'L (50)', 'XL (52)', '2XL (54)', '3XL (56)'],
            [61, 63, 65, 67.5, 70, 72.5],
            [62.5, 64, 65.5, 67, 68.5, 70],
            [24, 24.5, 25, 25.5, 26, 26.5],
        );
    }

    /**
     * Source: "Табель мер OMG детский.xlsx", rows A/E/S. The tech pack has no data
     * for size 170, so it's omitted here even though it's offered in the size picker.
     *
     * @return array<int, array{size: string, chest: string, length: string, sleeve: string}>
     */
    private function kidsSizeTable(): array
    {
        return $this->zipSizeTable(
            ['122', '128', '134', '140', '146', '152', '158', '164'],
            [43, 44.5, 46, 47.5, 49, 50.5, 52, 53.5],
            [57, 59, 61, 63, 65, 67, 69, 71],
            [16.5, 17.2, 17.9, 18.6, 19.3, 20, 20.7, 21.4],
        );
    }

    /**
     * @param  array<int, string>  $sizes
     * @param  array<int, float|int>  $chest
     * @param  array<int, float|int>  $length
     * @param  array<int, float|int>  $sleeve
     * @return array<int, array{size: string, chest: string, length: string, sleeve: string}>
     */
    private function zipSizeTable(array $sizes, array $chest, array $length, array $sleeve): array
    {
        return collect($sizes)
            ->map(fn (string $size, int $i): array => [
                'size' => $size,
                'chest' => (string) $chest[$i],
                'length' => (string) $length[$i],
                'sleeve' => (string) $sleeve[$i],
            ])
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, CustomizationService>
     */
    private function syncCustomizationServices(): Collection
    {
        $descriptions = [
            'Шелкография' => 'Стойкое нанесение для крупных тиражей и точного повторения фирменного цвета.',
            'Вышивка' => 'Фактурное нанесение логотипа и надписей для премиальных линеек.',
            'DTF / термопечать' => 'Полноцветные изображения и быстрый запуск после согласования макета.',
            'Вшивные ярлыки' => 'Замена или добавление брендированных ярлыков по техническому заданию.',
        ];

        return collect($descriptions)
            ->map(function (string $description, string $name) use ($descriptions): CustomizationService {
                return CustomizationService::query()->updateOrCreate(
                    ['name' => $name],
                    [
                        'description' => $description,
                        'sort_order' => array_search($name, array_keys($descriptions), true) ?: 0,
                        'is_active' => true,
                    ],
                );
            })
            ->values();
    }

    private function hideLegacyProducts(): void
    {
        Product::query()
            ->whereIn('slug', self::LEGACY_PRODUCT_SLUGS)
            ->update([
                'status' => ProductStatus::Inactive,
                'show_on_landing' => false,
            ]);
    }

    /**
     * @param  array<int, int>|null  $prices
     */
    private function syncPriceTiers(Product $product, ?array $prices, ?int $densityId = null, int $sortOrderOffset = 0): void
    {
        if ($prices === null) {
            $product->priceTiers()->where('density_id', $densityId)->delete();

            return;
        }

        $quantities = array_reverse(ProductPriceTier::publicQuantities());

        foreach ($quantities as $sortOrder => $quantity) {
            ProductPriceTier::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'density_id' => $densityId,
                    'quantity' => $quantity,
                    'currency' => ProductPriceTier::DEFAULT_CURRENCY,
                ],
                [
                    'unit_price' => $this->sourceRubPriceToUsd($prices[$quantity]),
                    'sort_order' => $sortOrderOffset + $sortOrder,
                ],
            );
        }

        $product->priceTiers()
            ->where('currency', ProductPriceTier::DEFAULT_CURRENCY)
            ->where('density_id', $densityId)
            ->whereNotIn('quantity', $quantities)
            ->delete();
    }

    private function sourceRubPriceToUsd(int $price): string
    {
        return number_format($price / self::SOURCE_RUB_TO_USD_RATE, 2, '.', '');
    }

    /**
     * @return array<int, array{
     *     slug: string,
     *     sku: string,
     *     name: string,
     *     category: string,
     *     density: ?string,
     *     fit: ?string,
     *     stock_conditions: string,
     *     availability_status: AvailabilityStatus,
     *     short_description: string,
     *     description: string,
     *     cover_image: string,
     *     prices: array<int, int>|null,
     *     size_names: array<int, string>,
     *     size_table: array<int, array{size: string, chest: string, length: string, sleeve: string}>
     * }>
     */
    private function productRows(): array
    {
        return [
            $this->variantRow('basic-tee-140-150', 'SH-TEE-145', 'Базовая футболка', 'Футболки', 'Regular Fit', [
                ['density' => '140-150 гр', 'prices' => [10000 => 165, 5000 => 170, 1000 => 175, 500 => 180, 100 => 185, 10 => 190]],
                ['density' => '160 гр', 'prices' => [10000 => 185, 5000 => 190, 1000 => 195, 500 => 200, 100 => 205, 10 => 210]],
                ['density' => '180 гр', 'prices' => [10000 => 205, 5000 => 210, 1000 => 215, 500 => 220, 100 => 225, 10 => 230]],
            ], '/brand/products/basic-tee-140-150.jpg', sizeNames: self::TEE_SIZES, sizeTable: $this->basicTeeSizeTable()),
            $this->variantRow('oversize-tee-180', 'SH-TEE-OVR-180', 'Оверсайз футболка', 'Футболки', 'Oversized', [
                ['density' => '180 гр', 'prices' => [10000 => 265, 5000 => 270, 1000 => 275, 500 => 280, 100 => 285, 10 => 290]],
                ['density' => '220 гр', 'prices' => [10000 => 315, 5000 => 320, 1000 => 325, 500 => 330, 100 => 335, 10 => 340]],
            ], '/brand/products/oversize-tee-180.jpg', sizeNames: self::OVERSIZE_SIZES, sizeTable: $this->oversizeSizeTable()),
            $this->row('kids-tee-175-185', 'SH-KIDS-TEE-180', 'Детские 180 гр', 'Детская одежда', '180 гр', 'Regular Fit', [10000 => 155, 5000 => 160, 1000 => 165, 500 => 170, 100 => 175, 10 => 180], '/brand/products/kids-tee-175-185.jpg', sizeNames: self::KIDS_SIZES, sizeTable: $this->kidsSizeTable()),
            $this->row('women-tee-180', 'SH-WOMEN-TEE-180', 'Женские 180 гр', 'Женская одежда', '180 гр', 'Regular Fit', [10000 => 200, 5000 => 205, 1000 => 210, 500 => 215, 100 => 220, 10 => 225], '/brand/products/women-tee-180.jpg'),
            $this->row('longsleeve-140-150', 'SH-LONG-145', 'Лонгслив 180 гр', 'Лонгсливы', '180 гр', 'Regular Fit', [10000 => 210, 5000 => 215, 1000 => 220, 500 => 225, 100 => 230, 10 => 235], '/brand/products/longsleeve-140-150.jpg'),
            $this->row('sweatshirt-two-thread-220-240', 'SH-SWEAT-2T-230', 'Свитшот 2х нитка 220-240 гр', 'Свитшоты', '220-240 гр', 'Regular Fit', [10000 => 390, 5000 => 395, 1000 => 400, 500 => 405, 100 => 410, 10 => 415], '/brand/products/sweatshirt-two-thread-220-240.jpg'),
            $this->row('hoodie-two-thread-220-240', 'SH-HOODIE-2T-230', 'Худи 2х нитка 320 гр', 'Худи', '320 гр', 'Regular Fit', [10000 => 520, 5000 => 525, 1000 => 530, 500 => 535, 100 => 540, 10 => 545], '/brand/products/hoodie-two-thread-220-240.jpg'),
            $this->row('hoodie-three-thread-260-280', 'SH-HOODIE-3T-270', 'Худи 3х нитка 320 гр', 'Худи', '320 гр', 'Regular Fit', [10000 => 935, 5000 => 940, 1000 => 945, 500 => 950, 100 => 955, 10 => 960], '/brand/products/hoodie-three-thread-260-280.jpg'),
            $this->row('baseball-cap', 'SH-CAP-BASE', 'Бейсболка', 'Аксессуары', null, null, [10000 => 115, 5000 => 120, 1000 => 125, 500 => 130, 100 => 135, 10 => 140], '/brand/products/baseball-cap.jpg', AvailabilityStatus::MadeToOrder, 'заказ'),
            $this->row('shopper', 'SH-SHOPPER', 'Шоппер', 'Аксессуары', null, null, null, '/brand/products/shopper.jpg', AvailabilityStatus::MadeToOrder, 'заказ'),
        ];
    }

    /**
     * @param  array<int, int>|null  $prices
     * @return array<string, mixed>
     */
    private function row(
        string $slug,
        string $sku,
        string $name,
        string $category,
        ?string $density,
        ?string $fit,
        ?array $prices,
        string $coverImage,
        AvailabilityStatus $availabilityStatus = AvailabilityStatus::InStock,
        string $stockConditions = 'склад/заказ',
        array $sizeNames = [],
        array $sizeTable = [],
    ): array {
        $shortDescription = $density
            ? "{$name}: бланковый текстиль, плотность {$density}."
            : "{$name}: бланковый текстиль.";

        return [
            'slug' => $slug,
            'sku' => $sku,
            'name' => $name,
            'category' => $category,
            'density' => $density,
            'fit' => $fit,
            'stock_conditions' => $stockConditions,
            'availability_status' => $availabilityStatus,
            'short_description' => $shortDescription,
            'description' => $shortDescription.' Цена «на заказ» актуальна для изменения цвета изделия или вшивных ярлыков; изменения фасона, ткани, фурнитуры и материалов для ярлыков рассчитывает менеджер.',
            'cover_image' => $coverImage,
            'prices' => $prices,
            'size_names' => $sizeNames,
            'size_table' => $sizeTable,
        ];
    }

    /**
     * @param  array<int, array{density: string, prices: array<int, int>}>  $densities
     * @return array<string, mixed>
     */
    private function variantRow(
        string $slug,
        string $sku,
        string $name,
        string $category,
        ?string $fit,
        array $densities,
        string $coverImage,
        AvailabilityStatus $availabilityStatus = AvailabilityStatus::InStock,
        string $stockConditions = 'склад/заказ',
        array $sizeNames = [],
        array $sizeTable = [],
    ): array {
        $densityLabel = implode(', ', array_column($densities, 'density'));
        $shortDescription = "{$name}: бланковый текстиль, плотности {$densityLabel}.";

        return [
            'slug' => $slug,
            'sku' => $sku,
            'name' => $name,
            'category' => $category,
            'densities' => $densities,
            'fit' => $fit,
            'stock_conditions' => $stockConditions,
            'availability_status' => $availabilityStatus,
            'short_description' => $shortDescription,
            'description' => $shortDescription.' Цена «на заказ» актуальна для изменения цвета изделия или вшивных ярлыков; изменения фасона, ткани, фурнитуры и материалов для ярлыков рассчитывает менеджер.',
            'cover_image' => $coverImage,
            'size_names' => $sizeNames,
            'size_table' => $sizeTable,
        ];
    }
}
