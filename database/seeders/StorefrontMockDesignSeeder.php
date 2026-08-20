<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Models\Catalog\Category;
use App\Models\Catalog\Color;
use App\Models\Catalog\Density;
use App\Models\Catalog\Product;
use App\Models\Content\Faq;
use App\Models\Content\HomePageContent;
use Illuminate\Database\Seeder;

class StorefrontMockDesignSeeder extends Seeder
{
    public function run(): void
    {
        $this->syncDictionaries();
        [$teeCategoryId, $otherCategoryId] = $this->syncCategories();

        $this->syncProducts($teeCategoryId, $otherCategoryId);
        $this->syncFaqs();
        $this->syncHomeContent();
    }

    private function syncDictionaries(): void
    {
        Color::query()
            ->where('name', 'По согласованию')
            ->update(['name' => 'Цвет по ТЗ']);

        Color::query()->firstOrCreate(
            ['name' => 'Цвет по ТЗ'],
            ['hex_code' => null, 'sort_order' => 7, 'is_active' => true],
        );

        foreach ([180, 200, 240, 340] as $sortOrder => $gsm) {
            Density::query()->updateOrCreate(
                ['gsm' => $gsm],
                [
                    'name' => "{$gsm} gsm",
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function syncCategories(): array
    {
        $teeCategory = $this->categoryForProduct('basic-tee-white')
            ?? Category::query()->orderBy('sort_order')->first()
            ?? Category::query()->create(['name' => 'Футболки', 'sort_order' => 0, 'is_active' => true]);

        $teeCategory->update([
            'name' => 'Футболки',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $otherCategory = $this->categoryForProduct('full-cycle-custom-production');

        if (! $otherCategory || $otherCategory->is($teeCategory)) {
            $otherCategory = Category::query()
                ->where('id', '!=', $teeCategory->id)
                ->whereIn('name', ['Другие модели', 'Индивидуальные проекты'])
                ->orderBy('sort_order')
                ->first()
                ?? Category::query()
                    ->where('id', '!=', $teeCategory->id)
                    ->orderBy('sort_order')
                    ->first()
                ?? Category::query()->create(['name' => 'Другие модели', 'sort_order' => 1, 'is_active' => true]);
        }

        $otherCategory->update([
            'name' => 'Другие модели',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Category::query()
            ->whereNotIn('id', [$teeCategory->id, $otherCategory->id])
            ->whereIn('name', ['Базовые футболки', 'Футболки под бренд', 'Индивидуальные проекты', 'Футболки'])
            ->update(['is_active' => false]);

        return [$teeCategory->id, $otherCategory->id];
    }

    private function categoryForProduct(string $slug): ?Category
    {
        return Product::query()
            ->with('category')
            ->where('slug', $slug)
            ->first()
            ?->category;
    }

    private function syncProducts(int $teeCategoryId, int $otherCategoryId): void
    {
        $products = [
            'basic-tee-white' => [
                'name' => 'Базовая футболка — белая',
                'category_id' => $teeCategoryId,
                'description' => 'Готовая партия базовых футболок без принта. Можно заказать брендирование, упаковку и маркировку.',
                'stock_conditions' => 'Готовая партия на складе — по текущим остаткам.',
                'featured' => true,
                'sort_order' => 0,
                'cover_image' => '/brand/catalog-white-v2.jpg',
            ],
            'basic-tee-black' => [
                'name' => 'Базовая футболка — чёрная',
                'category_id' => $teeCategoryId,
                'description' => 'Готовая партия базовых футболок без принта. Можно заказать брендирование, упаковку и маркировку.',
                'stock_conditions' => 'Готовая партия на складе — по текущим остаткам.',
                'featured' => true,
                'sort_order' => 1,
                'cover_image' => '/brand/catalog-black-v2.jpg',
            ],
            'brand-color-tee' => [
                'name' => 'Футболки в цвете бренда',
                'category_id' => $teeCategoryId,
                'description' => 'Пошив партии в нужном цвете, плотности и посадке. Перед запуском согласуем образец и параметры ткани.',
                'stock_conditions' => 'Производство под заказ после согласования образца.',
                'featured' => true,
                'sort_order' => 2,
                'cover_image' => '/brand/catalog-colors-v2.jpg',
            ],
            'heavy-oversize-tee' => [
                'name' => 'Футболка Heavy Oversize',
                'category_id' => $teeCategoryId,
                'description' => 'Плотная футболка свободной посадки для премиальной линейки маркетплейса. Производство под заказ.',
                'stock_conditions' => 'Производство под заказ после согласования образца.',
                'featured' => false,
                'sort_order' => 3,
                'cover_image' => '/brand/model-close.jpg',
            ],
            'full-cycle-custom-production' => [
                'name' => 'Худи, свитшоты и лонгсливы',
                'category_id' => $otherCategoryId,
                'description' => 'Разработаем или адаптируем модель под ваш бренд. Лекала, образец, пошив, упаковка и маркировка партии.',
                'stock_conditions' => 'Индивидуальный график производства по спецификации.',
                'featured' => false,
                'sort_order' => 4,
                'cover_image' => '/brand/model-motion.jpg',
            ],
        ];

        foreach ($products as $slug => $attributes) {
            Product::query()
                ->where('slug', $slug)
                ->update($attributes + [
                    'status' => ProductStatus::Active->value,
                    'show_on_landing' => true,
                    'moq' => 5000,
                ]);
        }
    }

    private function syncFaqs(): void
    {
        $faqs = [
            [
                'question' => 'Какой минимальный заказ?',
                'answer' => 'Минимальная производственная партия — 5 000 изделий. Параметры одной серии фиксируются в спецификации.',
            ],
            [
                'question' => 'Что сейчас есть на складе?',
                'answer' => 'Белые и чёрные футболки. Перед заявкой менеджер уточнит актуальные размеры и остатки.',
            ],
            [
                'question' => 'Можно заказать другие цвета?',
                'answer' => 'Да. Цвет, плотность, посадку и размерную сетку согласуем перед запуском партии.',
            ],
            [
                'question' => 'Почему на сайте нет фиксированных цен?',
                'answer' => 'Цена зависит от ткани, комплектации, нанесения и объёма. Итог фиксируется в спецификации.',
            ],
        ];

        foreach ($faqs as $sortOrder => $faq) {
            $model = Faq::query()
                ->where('sort_order', $sortOrder)
                ->first()
                ?? Faq::query()->where('question', $faq['question'])->first()
                ?? new Faq;

            $model->fill($faq + [
                'sort_order' => $sortOrder,
                'is_active' => true,
            ]);

            $model->save();
        }
    }

    private function syncHomeContent(): void
    {
        $homeContent = HomePageContent::query()->firstOrCreate(['id' => 1], ['content' => []]);

        $homeContent->update([
            'content' => array_replace_recursive($homeContent->content ?? [], [
                'nav' => [
                    'apply_button' => 'Заявка',
                ],
                'hero' => [
                    'headline_main' => 'База, на которой',
                    'headline_accent' => 'строятся бренды',
                    'cta_secondary' => 'Запросить расчёт',
                    'hero_badge_value' => '180–340',
                    'hero_badge_label' => 'gsm под заказ',
                    'top_ticker' => 'ПАРТИИ ОТ 5 000 ШТУК • БЕЛЫЕ И ЧЁРНЫЕ ФУТБОЛКИ НА СКЛАДЕ',
                    'bottom_ticker' => 'БЕЗ ПОСРЕДНИКОВ ✦ КОНТРОЛЬ КАЧЕСТВА ✦ ПАРТИИ ОТ 5 000 ШТ. ✦ ЦЕНА ПО СПЕЦИФИКАЦИИ',
                ],
                'catalog' => [
                    'count_label' => '5 моделей',
                    'kicker' => 'Футболки',
                ],
                'sellers' => [
                    'heading' => 'Не просто поставщик.',
                    'subheading' => 'Производственная база.',
                    'caption' => 'КОНТРОЛЬ ПОЛОТНА ВРУЧНУЮ',
                ],
                'portal_callout' => [
                    'cta' => 'Начать с расчёта партии',
                ],
                'footer' => [
                    'made_for' => 'СДЕЛАНО ДЛЯ ТЕХ, КТО СТРОИТ СВОЁ.',
                ],
            ]),
        ]);
    }
}
