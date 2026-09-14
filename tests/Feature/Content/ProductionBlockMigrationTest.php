<?php

use App\Models\Content\HomePageContent;

function seedPreProductionTextContent(): void
{
    HomePageContent::query()->create(['content' => [
        'process' => [
            'eyebrow' => 'О производстве',
            'heading' => 'Все идут привычным путём.',
            'subheading' => 'Мы сделали свой ход.',
            'stages' => [
                ['title' => 'Хлопок', 'subtitle' => 'СЫРЬЁ НА МЕСТЕ'],
            ],
        ],
        'sellers' => ['eyebrow' => 'Производственная база'],
    ]]);
}

test('the production block migration adds the factory intro and CTA', function () {
    seedPreProductionTextContent();

    $migration = include database_path('migrations/2026_09_14_000006_update_production_block_text.php');
    $migration->up();

    $content = HomePageContent::query()->firstOrFail();

    expect($content->get('process.intro'))->toBe('Организуем производство крупных и кастомных партий напрямую на фабриках, которые работают под наши заказы. Учитываем требования к модели, материалу, плотности, цвету и размерному ряду. Контролируем качество на производстве и перед отгрузкой, а также сопровождаем работу с рекламациями.');
    expect($content->get('process.cta'))->toBe('Запросить расчёт');

    expect($content->get('process.heading'))->toBe('Все идут привычным путём.');
    expect(collect($content->get('process.stages')))->toHaveCount(1);
    expect($content->get('sellers.eyebrow'))->toBe('Производственная база');
});

test('the production block migration is idempotent', function () {
    seedPreProductionTextContent();

    $migration = include database_path('migrations/2026_09_14_000006_update_production_block_text.php');
    $migration->up();
    $migration->up();

    expect(HomePageContent::query()->firstOrFail()->get('process.cta'))->toBe('Запросить расчёт');
});

test('the production block migration is a no-op when no content row exists', function () {
    $migration = include database_path('migrations/2026_09_14_000006_update_production_block_text.php');

    $migration->up();

    expect(HomePageContent::query()->count())->toBe(0);
});
