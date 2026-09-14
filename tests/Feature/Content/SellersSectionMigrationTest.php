<?php

use App\Models\Content\HomePageContent;

function seedPreB2bSellersContent(): void
{
    HomePageContent::query()->create(['content' => [
        'sellers' => [
            'eyebrow' => 'Для продавцов маркетплейсов',
            'heading' => 'Не просто поставщик.',
            'subheading' => 'Производственная база.',
            'cta' => 'Обсудить техническое задание',
            'items' => [
                ['label' => 'Футболки, лонгсливы, свитшоты и худи'],
                ['label' => 'Цены по тиражам от 100 до 5 000 штук'],
            ],
            'caption' => 'КОНТРОЛЬ ПОЛОТНА ВРУЧНУЮ',
        ],
        'catalog' => ['heading' => 'Бланковый текстиль'],
    ]]);
}

test('the sellers migration repositions the section as universal B2B', function () {
    seedPreB2bSellersContent();

    $migration = include database_path('migrations/2026_09_14_000005_update_sellers_section_to_b2b_positioning.php');
    $migration->up();

    $content = HomePageContent::query()->firstOrFail();

    expect($content->get('sellers.eyebrow'))->toBe('Производственная база');
    expect($content->get('sellers.heading'))->toBe('Изготовим партию');
    expect($content->get('sellers.subheading'))->toBe('под ваш бренд и требования');
    expect($content->get('sellers.intro'))->toBe('Помогаем подготовить крупную партию: согласуем размерную матрицу, упаковку, этикетки и маркировку.');
    expect($content->get('sellers.cta'))->toBe('Запросить расчёт');
    expect(collect($content->get('sellers.items'))->pluck('label')->all())->toBe([
        'Футболки, лонгсливы, худи и другие базовые изделия',
        'Заказ от 100 шт.',
        'Крупные партии без верхнего ограничения',
        'Плотности из прайса как отдельные модели',
        'Упаковка, этикетки и маркировка',
        'Повтор серии по образцу',
    ]);

    expect($content->get('sellers.caption'))->toBe('КОНТРОЛЬ ПОЛОТНА ВРУЧНУЮ');
    expect($content->get('catalog.heading'))->toBe('Бланковый текстиль');
});

test('the sellers migration is idempotent', function () {
    seedPreB2bSellersContent();

    $migration = include database_path('migrations/2026_09_14_000005_update_sellers_section_to_b2b_positioning.php');
    $migration->up();
    $migration->up();

    $content = HomePageContent::query()->firstOrFail();
    expect($content->get('sellers.eyebrow'))->toBe('Производственная база');
    expect(collect($content->get('sellers.items')))->toHaveCount(6);
});

test('the sellers migration is a no-op when no content row exists', function () {
    $migration = include database_path('migrations/2026_09_14_000005_update_sellers_section_to_b2b_positioning.php');

    $migration->up();

    expect(HomePageContent::query()->count())->toBe(0);
});
