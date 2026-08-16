<?php

namespace Database\Seeders;

use App\Enums\AvailabilityStatus;
use App\Enums\ProductStatus;
use App\Models\Catalog\Category;
use App\Models\Catalog\Color;
use App\Models\Catalog\CustomizationService;
use App\Models\Catalog\Density;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use App\Models\Catalog\Size;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect(['Базовые футболки', 'Футболки под бренд', 'Индивидуальные проекты'])
            ->map(fn (string $name, int $i) => Category::factory()->create([
                'name' => $name,
                'sort_order' => $i,
            ]));

        $colors = collect([
            'Молочный' => '#F5F0E6',
            'Графит' => '#4A4A4A',
            'Хаки' => '#8B7355',
            'Синий' => '#2C3E6B',
            'Розовый' => '#E5187E',
            'Белый' => '#FFFFFF',
            'Чёрный' => '#0D0802',
            'По согласованию' => null,
        ])->map(fn (?string $hex, string $name) => Color::factory()->create([
            'name' => $name,
            'hex_code' => $hex,
        ]))->values();

        $sizes = collect(['XS', 'S', 'M', 'L', 'XL', 'XXL'])
            ->map(fn (string $name, int $i) => Size::factory()->create([
                'name' => $name,
                'sort_order' => $i,
            ]));

        $densities = collect([180, 200, 240, 340])
            ->map(fn (int $gsm, int $i) => Density::factory()->create([
                'name' => "{$gsm} г/м²",
                'gsm' => $gsm,
                'sort_order' => $i,
            ]));

        $serviceDescriptions = [
            'Шелкография' => 'Стойкое нанесение для крупных тиражей и точного повторения фирменного цвета.',
            'Вышивка' => 'Фактурное нанесение логотипа и надписей для премиальных линеек.',
            'DTF / термопечать' => 'Полноцветные изображения и быстрый запуск после согласования макета.',
            'Разработка лекал' => 'Собственная посадка, образец и градация размерного ряда для серии.',
        ];

        $services = collect(array_keys($serviceDescriptions))
            ->map(fn (string $name, int $i) => CustomizationService::factory()->create([
                'name' => $name,
                'description' => $serviceDescriptions[$name],
                'sort_order' => $i,
            ]));

        $findColor = fn (string $name) => $colors->firstWhere('name', $name);
        $allSizeIds = $sizes->pluck('id');

        $this->createVariants(
            $this->createWhiteTee($categories->get(0)),
            colors: collect([$findColor('Белый')]),
            densities: collect([$densities->get(0)]),
            sizeIds: $allSizeIds,
            availability: AvailabilityStatus::InStock,
            stockQuantity: 320,
        );

        $this->createVariants(
            $this->createBlackTee($categories->get(0)),
            colors: collect([$findColor('Чёрный')]),
            densities: collect([$densities->get(0)]),
            sizeIds: $allSizeIds,
            availability: AvailabilityStatus::InStock,
            stockQuantity: 280,
        );

        $brandTee = $this->createBrandColorTee($categories->get(1));
        $brandTee->customizationServices()->attach($services->pluck('id'));
        $this->createVariants(
            $brandTee,
            colors: collect(['Молочный', 'Графит', 'Хаки', 'Синий', 'Розовый'])->map($findColor),
            densities: $densities->take(3),
            sizeIds: $allSizeIds,
            availability: AvailabilityStatus::MadeToOrder,
            stockQuantity: null,
        );

        $oversize = $this->createHeavyOversizeTee($categories->get(1));
        $oversize->customizationServices()->attach($services->firstWhere('name', 'Разработка лекал')->id);
        $this->createVariants(
            $oversize,
            colors: collect([$findColor('По согласованию')]),
            densities: collect([$densities->get(2)]),
            sizeIds: $allSizeIds,
            availability: AvailabilityStatus::MadeToOrder,
            stockQuantity: null,
        );

        $custom = $this->createFullCycleProduct($categories->get(2));
        $custom->customizationServices()->attach($services->pluck('id'));
        $this->createVariants(
            $custom,
            colors: collect([$findColor('По согласованию')]),
            densities: collect([$densities->get(3)]),
            sizeIds: $allSizeIds,
            availability: AvailabilityStatus::MadeToOrder,
            stockQuantity: null,
        );
    }

    private function createWhiteTee(Category $category): Product
    {
        return Product::factory()->create([
            'name' => 'Базовая футболка — белая',
            'category_id' => $category->id,
            'slug' => 'basic-tee-white',
            'sku' => 'SKU-TEE-WHITE',
            'short_description' => 'Готовая партия белых футболок из хлопка. Подходит для продаж без принта или нанесения вашего бренда.',
            'description' => 'Готовая партия белых футболок из хлопка. Подходит для продаж без принта или нанесения вашего бренда.',
            'composition' => '100% хлопок',
            'fit' => 'Regular Fit',
            'moq' => 5000,
            'stock_conditions' => 'Готовая партия на складе — по текущим остаткам.',
            'status' => ProductStatus::Active,
            'featured' => true,
            'show_on_landing' => true,
            'sort_order' => 0,
            'cover_image' => 'https://placehold.co/600x800/f5f0e6/0d0802?text=Белая+футболка',
        ]);
    }

    private function createBlackTee(Category $category): Product
    {
        return Product::factory()->create([
            'name' => 'Базовая футболка — чёрная',
            'category_id' => $category->id,
            'slug' => 'basic-tee-black',
            'sku' => 'SKU-TEE-BLACK',
            'short_description' => 'Готовая партия чёрных футболок из хлопка. Подходит для продаж без принта или нанесения вашего бренда.',
            'description' => 'Готовая партия чёрных футболок из хлопка. Подходит для продаж без принта или нанесения вашего бренда.',
            'composition' => '100% хлопок',
            'fit' => 'Regular Fit',
            'moq' => 5000,
            'stock_conditions' => 'Готовая партия на складе — по текущим остаткам.',
            'status' => ProductStatus::Active,
            'featured' => true,
            'show_on_landing' => true,
            'sort_order' => 1,
            'cover_image' => 'https://placehold.co/600x800/0d0802/f5f0e6?text=Чёрная+футболка',
        ]);
    }

    private function createBrandColorTee(Category $category): Product
    {
        return Product::factory()->create([
            'name' => 'Футболки в цвете бренда',
            'category_id' => $category->id,
            'slug' => 'brand-color-tee',
            'sku' => 'SKU-TEE-BRAND',
            'short_description' => 'Пошив партии в нужном цвете, плотности и посадке. Перед запуском согласуем образец и параметры ткани.',
            'description' => 'Пошив партии в нужном цвете, плотности и посадке. Перед запуском согласуем образец и параметры ткани.',
            'composition' => '100% хлопок',
            'fit' => 'Regular Fit',
            'moq' => 5000,
            'stock_conditions' => 'Производство под заказ после согласования образца.',
            'status' => ProductStatus::Active,
            'featured' => true,
            'show_on_landing' => true,
            'sort_order' => 2,
            'cover_image' => 'https://placehold.co/600x800/2c3e6b/ffffff?text=Цвет+бренда',
        ]);
    }

    private function createHeavyOversizeTee(Category $category): Product
    {
        return Product::factory()->create([
            'name' => 'Футболка Heavy Oversize',
            'category_id' => $category->id,
            'slug' => 'heavy-oversize-tee',
            'sku' => 'SKU-TEE-OVERSIZE',
            'short_description' => 'Плотная футболка свободной посадки для премиальной линейки маркетплейса. Производство под заказ.',
            'description' => 'Плотная футболка свободной посадки для премиальной линейки маркетплейса. Производство под заказ.',
            'composition' => '100% хлопок',
            'fit' => 'Oversized',
            'moq' => 5000,
            'stock_conditions' => 'Производство под заказ после согласования образца.',
            'status' => ProductStatus::Active,
            'featured' => false,
            'show_on_landing' => true,
            'sort_order' => 3,
            'cover_image' => 'https://placehold.co/600x800/8b7355/ffffff?text=Heavy+Oversize',
        ]);
    }

    private function createFullCycleProduct(Category $category): Product
    {
        return Product::factory()->create([
            'name' => 'Полный цикл: лекала, пошив, упаковка',
            'category_id' => $category->id,
            'slug' => 'full-cycle-custom-production',
            'sku' => 'SKU-TEE-CUSTOM',
            'short_description' => 'Лекала, образец, пошив, упаковка и маркировка партии.',
            'description' => 'Лекала, образец, пошив, упаковка и маркировка партии.',
            'composition' => 'По техническому заданию',
            'fit' => null,
            'moq' => 5000,
            'stock_conditions' => 'Индивидуальный график производства по спецификации.',
            'status' => ProductStatus::Active,
            'featured' => false,
            'show_on_landing' => true,
            'sort_order' => 4,
            'cover_image' => 'https://placehold.co/600x800/4a4a4a/ffffff?text=Индивидуальный+проект',
        ]);
    }

    /**
     * @param  Collection<int, Color>  $colors
     * @param  Collection<int, Density>  $densities
     * @param  Collection<int, int>  $sizeIds
     */
    private function createVariants(
        Product $product,
        Collection $colors,
        Collection $densities,
        Collection $sizeIds,
        AvailabilityStatus $availability,
        ?int $stockQuantity,
    ): void {
        foreach ($colors as $color) {
            foreach ($densities as $density) {
                foreach ($sizeIds as $sizeId) {
                    ProductVariant::factory()->create([
                        'product_id' => $product->id,
                        'color_id' => $color->id,
                        'size_id' => $sizeId,
                        'density_id' => $density->id,
                        'availability_status' => $availability,
                        'stock_quantity' => $stockQuantity,
                    ]);
                }
            }
        }
    }
}
