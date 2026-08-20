<?php

namespace Database\Seeders;

use App\Enums\AvailabilityStatus;
use App\Enums\ProductStatus;
use App\Models\Catalog\Category;
use App\Models\Catalog\Color;
use App\Models\Catalog\CustomizationService;
use App\Models\Catalog\Density;
use App\Models\Catalog\Product;
use App\Models\Catalog\Size;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect(['Футболки', 'Другие модели'])
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
            'Цвет по ТЗ' => null,
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
                'name' => "{$gsm} gsm",
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

        $this->attachVariantAxes(
            $this->createWhiteTee($categories->get(0)),
            colors: collect([$findColor('Белый')]),
            densities: collect([$densities->get(0)]),
            sizeIds: $allSizeIds,
        );

        $this->attachVariantAxes(
            $this->createBlackTee($categories->get(0)),
            colors: collect([$findColor('Чёрный')]),
            densities: collect([$densities->get(0)]),
            sizeIds: $allSizeIds,
        );

        $brandTee = $this->createBrandColorTee($categories->get(0));
        $brandTee->customizationServices()->attach($services->pluck('id'));
        $this->attachVariantAxes(
            $brandTee,
            colors: collect(['Молочный', 'Графит', 'Хаки', 'Синий', 'Розовый'])->map($findColor),
            densities: $densities->take(3),
            sizeIds: $allSizeIds,
        );

        $oversize = $this->createHeavyOversizeTee($categories->get(0));
        $oversize->customizationServices()->attach($services->firstWhere('name', 'Разработка лекал')->id);
        $this->attachVariantAxes(
            $oversize,
            colors: collect([$findColor('Цвет по ТЗ')]),
            densities: collect([$densities->get(2)]),
            sizeIds: $allSizeIds,
        );

        $custom = $this->createFullCycleProduct($categories->get(1));
        $custom->customizationServices()->attach($services->pluck('id'));
        $this->attachVariantAxes(
            $custom,
            colors: collect([$findColor('Цвет по ТЗ')]),
            densities: collect([$densities->get(3)]),
            sizeIds: $allSizeIds,
        );
    }

    private function createWhiteTee(Category $category): Product
    {
        return Product::factory()->create([
            'name' => 'Базовая футболка — белая',
            'category_id' => $category->id,
            'slug' => 'basic-tee-white',
            'sku' => 'SKU-TEE-WHITE',
            'description' => 'Готовая партия базовых футболок без принта. Можно заказать брендирование, упаковку и маркировку.',
            'composition' => '100% хлопок',
            'fit' => 'Regular Fit',
            'moq' => 5000,
            'stock_conditions' => 'Готовая партия на складе — по текущим остаткам.',
            'availability_status' => AvailabilityStatus::InStock,
            'stock_quantity' => 320,
            'status' => ProductStatus::Active,
            'featured' => true,
            'show_on_landing' => true,
            'sort_order' => 0,
            'cover_image' => '/brand/catalog-white-v2.jpg',
        ]);
    }

    private function createBlackTee(Category $category): Product
    {
        return Product::factory()->create([
            'name' => 'Базовая футболка — чёрная',
            'category_id' => $category->id,
            'slug' => 'basic-tee-black',
            'sku' => 'SKU-TEE-BLACK',
            'description' => 'Готовая партия базовых футболок без принта. Можно заказать брендирование, упаковку и маркировку.',
            'composition' => '100% хлопок',
            'fit' => 'Regular Fit',
            'moq' => 5000,
            'stock_conditions' => 'Готовая партия на складе — по текущим остаткам.',
            'availability_status' => AvailabilityStatus::InStock,
            'stock_quantity' => 280,
            'status' => ProductStatus::Active,
            'featured' => true,
            'show_on_landing' => true,
            'sort_order' => 1,
            'cover_image' => '/brand/catalog-black-v2.jpg',
        ]);
    }

    private function createBrandColorTee(Category $category): Product
    {
        return Product::factory()->create([
            'name' => 'Футболки в цвете бренда',
            'category_id' => $category->id,
            'slug' => 'brand-color-tee',
            'sku' => 'SKU-TEE-BRAND',
            'description' => 'Пошив партии в нужном цвете, плотности и посадке. Перед запуском согласуем образец и параметры ткани.',
            'composition' => '100% хлопок',
            'fit' => 'Regular Fit',
            'moq' => 5000,
            'stock_conditions' => 'Производство под заказ после согласования образца.',
            'availability_status' => AvailabilityStatus::MadeToOrder,
            'stock_quantity' => null,
            'status' => ProductStatus::Active,
            'featured' => true,
            'show_on_landing' => true,
            'sort_order' => 2,
            'cover_image' => '/brand/catalog-colors-v2.jpg',
        ]);
    }

    private function createHeavyOversizeTee(Category $category): Product
    {
        return Product::factory()->create([
            'name' => 'Футболка Heavy Oversize',
            'category_id' => $category->id,
            'slug' => 'heavy-oversize-tee',
            'sku' => 'SKU-TEE-OVERSIZE',
            'description' => 'Плотная футболка свободной посадки для премиальной линейки маркетплейса. Производство под заказ.',
            'composition' => '100% хлопок',
            'fit' => 'Oversized',
            'moq' => 5000,
            'stock_conditions' => 'Производство под заказ после согласования образца.',
            'availability_status' => AvailabilityStatus::MadeToOrder,
            'stock_quantity' => null,
            'status' => ProductStatus::Active,
            'featured' => false,
            'show_on_landing' => true,
            'sort_order' => 3,
            'cover_image' => '/brand/model-close.jpg',
        ]);
    }

    private function createFullCycleProduct(Category $category): Product
    {
        return Product::factory()->create([
            'name' => 'Худи, свитшоты и лонгсливы',
            'category_id' => $category->id,
            'slug' => 'full-cycle-custom-production',
            'sku' => 'SKU-TEE-CUSTOM',
            'description' => 'Разработаем или адаптируем модель под ваш бренд. Лекала, образец, пошив, упаковка и маркировка партии.',
            'composition' => 'По техническому заданию',
            'fit' => null,
            'moq' => 5000,
            'stock_conditions' => 'Индивидуальный график производства по спецификации.',
            'availability_status' => AvailabilityStatus::MadeToOrder,
            'stock_quantity' => null,
            'status' => ProductStatus::Active,
            'featured' => false,
            'show_on_landing' => true,
            'sort_order' => 4,
            'cover_image' => '/brand/model-motion.jpg',
        ]);
    }

    /**
     * @param  Collection<int, Color>  $colors
     * @param  Collection<int, Density>  $densities
     * @param  Collection<int, int>  $sizeIds
     */
    private function attachVariantAxes(
        Product $product,
        Collection $colors,
        Collection $densities,
        Collection $sizeIds,
    ): void {
        $product->colors()->attach($colors->pluck('id'));
        $product->densities()->attach($densities->pluck('id'));
        $product->sizes()->attach($sizeIds);
    }
}
