<?php

use App\Models\Content\HomePageContent;

function seedPreCtaRebuildContent(): void
{
    HomePageContent::query()->create(['content' => [
        'cta_section' => [
            'eyebrow' => 'Начать сотрудничество',
            'heading_main' => 'Пора сделать',
            'heading_accent' => 'свой ход.',
            'subcopy' => 'Сформируйте черновик заявки: выберите товары в каталоге, укажите количество по каждой позиции и оставьте контакты для расчёта.',
            'email' => 'info@svojkhod.ru',
        ],
        'form' => [
            'submit' => 'Сформировать заявку',
            'company' => 'КОМПАНИЯ',
        ],
    ]]);
}

test('the final CTA migration rebuilds the section around one price-request action', function () {
    seedPreCtaRebuildContent();

    $migration = include database_path('migrations/2026_09_14_000008_rebuild_final_cta_section.php');
    $migration->up();

    $content = HomePageContent::query()->firstOrFail();

    expect($content->get('cta_section.heading'))->toBe('Получите оптовый прайс и актуальное наличие');
    expect($content->get('cta_section.heading_main'))->toBeNull();
    expect($content->get('cta_section.heading_accent'))->toBeNull();
    expect($content->get('cta_section.subcopy'))->toBe('Подберём изделия под ваш тираж и задачу, сообщим стоимость и сроки поставки. Работаем с заказами от 100 шт. без верхнего ограничения по объёму.');
    expect($content->get('cta_section.trust_line'))->toBe('Склад в Москве • Оплата по расчётному счёту • Полный комплект документов');
    expect($content->get('form.submit'))->toBe('Запросить прайс');

    expect($content->get('cta_section.eyebrow'))->toBe('Начать сотрудничество');
    expect($content->get('cta_section.email'))->toBe('info@svojkhod.ru');
    expect($content->get('form.company'))->toBe('КОМПАНИЯ');
});

test('the final CTA migration is idempotent', function () {
    seedPreCtaRebuildContent();

    $migration = include database_path('migrations/2026_09_14_000008_rebuild_final_cta_section.php');
    $migration->up();
    $migration->up();

    expect(HomePageContent::query()->firstOrFail()->get('form.submit'))->toBe('Запросить прайс');
});

test('the final CTA migration is a no-op when no content row exists', function () {
    $migration = include database_path('migrations/2026_09_14_000008_rebuild_final_cta_section.php');

    $migration->up();

    expect(HomePageContent::query()->count())->toBe(0);
});
