<?php

use App\Models\Content\HomePageContent;

function seedPreSweepContent(): void
{
    HomePageContent::query()->create(['content' => [
        'hero' => [
            'hero_badge_value' => '100–5 000',
            'hero_badge_label' => 'шт. по прайсу',
            'top_ticker' => 'БЛАНКОВЫЙ ТЕКСТИЛЬ • ТИРАЖИ ОТ 100 ДО 5 000 ШТ.',
            'headline_main' => 'Бланковый текстиль крупным оптом —',
        ],
        'sellers' => [
            'cta' => 'Запросить расчёт',
            'heading' => 'Изготовим партию',
        ],
        'process' => [
            'cta' => 'Запросить расчёт',
            'eyebrow' => 'О производстве',
        ],
        'footer' => [
            'order_items' => [
                ['label' => 'На складе'],
                ['label' => 'Под заказ'],
                ['label' => 'Запросить расчёт'],
                ['label' => 'Вопросы'],
            ],
        ],
    ]]);
}

test('the sitewide consistency migration unifies badge, ticker and CTA copy', function () {
    seedPreSweepContent();

    $migration = include database_path('migrations/2026_09_14_000009_sitewide_consistency_sweep.php');
    $migration->up();

    $content = HomePageContent::query()->firstOrFail();

    expect($content->get('hero.hero_badge_value'))->toBe('от 100');
    expect($content->get('hero.hero_badge_label'))->toBe('шт. без ограничений');
    expect($content->get('hero.top_ticker'))->toBe('БЛАНКОВЫЙ ТЕКСТИЛЬ • ТИРАЖИ ОТ 100 ШТ. БЕЗ ВЕРХНЕГО ОГРАНИЧЕНИЯ');
    expect($content->get('sellers.cta'))->toBe('Запросить прайс');
    expect($content->get('process.cta'))->toBe('Запросить прайс');
    expect($content->get('footer.order_items.2.label'))->toBe('Запросить прайс');

    expect($content->get('hero.headline_main'))->toBe('Бланковый текстиль крупным оптом —');
    expect($content->get('sellers.heading'))->toBe('Изготовим партию');
    expect($content->get('process.eyebrow'))->toBe('О производстве');
    expect($content->get('footer.order_items.0.label'))->toBe('На складе');
    expect($content->get('footer.order_items.1.label'))->toBe('Под заказ');
    expect($content->get('footer.order_items.3.label'))->toBe('Вопросы');
});

test('the sitewide consistency migration is idempotent', function () {
    seedPreSweepContent();

    $migration = include database_path('migrations/2026_09_14_000009_sitewide_consistency_sweep.php');
    $migration->up();
    $migration->up();

    expect(HomePageContent::query()->firstOrFail()->get('sellers.cta'))->toBe('Запросить прайс');
});

test('the sitewide consistency migration down() restores the prior copy', function () {
    seedPreSweepContent();

    $migration = include database_path('migrations/2026_09_14_000009_sitewide_consistency_sweep.php');
    $migration->up();
    $migration->down();

    $content = HomePageContent::query()->firstOrFail();

    expect($content->get('hero.hero_badge_value'))->toBe('100–5 000');
    expect($content->get('hero.hero_badge_label'))->toBe('шт. по прайсу');
    expect($content->get('hero.top_ticker'))->toBe('БЛАНКОВЫЙ ТЕКСТИЛЬ • ТИРАЖИ ОТ 100 ДО 5 000 ШТ.');
    expect($content->get('sellers.cta'))->toBe('Запросить расчёт');
    expect($content->get('process.cta'))->toBe('Запросить расчёт');
    expect($content->get('footer.order_items.2.label'))->toBe('Запросить расчёт');
});

test('the sitewide consistency migration is a no-op when no content row exists', function () {
    $migration = include database_path('migrations/2026_09_14_000009_sitewide_consistency_sweep.php');

    $migration->up();

    expect(HomePageContent::query()->count())->toBe(0);
});
