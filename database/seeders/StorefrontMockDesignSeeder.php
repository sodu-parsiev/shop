<?php

namespace Database\Seeders;

use App\Models\Content\Faq;
use App\Models\Content\HomePageContent;
use Illuminate\Database\Seeder;

class StorefrontMockDesignSeeder extends Seeder
{
    public function run(): void
    {
        $this->syncFaqs();
        $this->syncHomeContent();
    }

    private function syncFaqs(): void
    {
        $faqs = [
            [
                'question' => 'Какой минимальный заказ?',
                'answer' => 'В прайсе доступны тиражи от 10 изделий. Итоговую заявку менеджер фиксирует после согласования модели, нанесения и комплектации.',
            ],
            [
                'question' => 'Что сейчас есть на складе?',
                'answer' => 'Прайс включает позиции со статусом «склад/заказ» и «заказ». Актуальные остатки и сроки менеджер уточнит перед запуском.',
            ],
            [
                'question' => 'Можно заказать другие цвета?',
                'answer' => 'Да. Цвет, плотность, посадку и размерную сетку согласуем перед запуском партии.',
            ],
            [
                'question' => 'Почему на сайте нет фиксированных цен?',
                'answer' => 'На карточках указана цена чистого текстиля без нанесения. Нанесение, изменение фасона, ткани, фурнитуры и ярлыков рассчитывает менеджер.',
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
                    'hero_badge_value' => '10–10 000',
                    'hero_badge_label' => 'шт. по прайсу',
                    'top_ticker' => 'ЧИСТЫЙ ТЕКСТИЛЬ БЕЗ НАНЕСЕНИЯ • ТИРАЖИ ОТ 10 ДО 10 000 ШТ.',
                    'bottom_ticker' => 'БЕЗ ПОСРЕДНИКОВ ✦ КОНТРОЛЬ КАЧЕСТВА ✦ ЦЕНА ПО ПРАЙСУ ✦ ИНДИВИДУАЛЬНЫЙ РАСЧЁТ НАНЕСЕНИЯ',
                ],
                'catalog' => [
                    'count_label' => '14 моделей',
                    'price_note' => 'Цены указаны за чистый текстиль без нанесения.',
                    'kicker' => 'Текстиль',
                    'price_label' => 'Цена за штуку',
                    'price_value' => 'По прайсу',
                    'price_note_small' => 'чистый текстиль',
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
