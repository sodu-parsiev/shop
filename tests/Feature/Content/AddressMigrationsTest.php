<?php

use App\Models\Content\HomePageContent;
use App\Models\Content\Page;
use Database\Seeders\HomePageContentSeeder;
use Database\Seeders\LegalPagesSeeder;
use Illuminate\Database\Migrations\Migration;

const KOTELNIKI_ADDRESS = 'Московская область, Котельники, Дзержинское шоссе, 13';

const MOSCOW_OFFICE_ADDRESS = 'г. Москва, ул. Вишнёвая, д. 9, корп. 1, бизнес-центр «Империал»';

function contactsAddressMigration(): Migration
{
    return require database_path('migrations/2026_10_21_000004_use_moscow_office_address_in_contacts.php');
}

function legalAddressLabelMigration(): Migration
{
    return require database_path('migrations/2026_10_21_000005_label_legal_address_on_legal_pages.php');
}

test('the contacts migration switches to the Moscow office address and keeps the other contacts', function () {
    HomePageContent::query()->create(['content' => [
        'cta_section' => [
            'email' => 'info@svoyhod.ru',
            'address_label' => 'Адрес:',
            'address' => KOTELNIKI_ADDRESS,
        ],
    ]]);

    contactsAddressMigration()->up();

    $content = HomePageContent::query()->firstOrFail();

    expect($content->get('cta_section.address'))->toBe(MOSCOW_OFFICE_ADDRESS)
        ->and($content->get('cta_section.address_label'))->toBe('Адрес:')
        ->and($content->get('cta_section.email'))->toBe('info@svoyhod.ru');

    contactsAddressMigration()->down();

    expect($content->fresh()->get('cta_section.address'))->toBe(KOTELNIKI_ADDRESS);
});

test('the contacts migration is a no-op without a content row', function () {
    contactsAddressMigration()->up();

    expect(HomePageContent::query()->exists())->toBeFalse();
});

test('the legal-label migration labels the bare Ivanovo address on the policy and consent only', function () {
    $privacy = Page::factory()->create([
        'slug' => 'privacy',
        'body' => "1. Оператор (ИНН 3700025101, ОГРН 1243700009915, юридический адрес: 153025, г. Иваново, Химический переулок, д. 2).\n\n10. Контакты оператора\n\nООО «ОМГ АППАРЕЛЬ», ИНН 3700025101, 153025, г. Иваново, Химический переулок, д. 2, email: info@svoyhod.ru.",
    ]);
    $consent = Page::factory()->create([
        'slug' => 'consent',
        'body' => 'Я даю ООО «ОМГ АППАРЕЛЬ» (ИНН 3700025101, ОГРН 1243700009915, 153025, г. Иваново, Химический переулок, д. 2) согласие.',
    ]);
    $requisites = Page::factory()->create([
        'slug' => 'requisites',
        'body' => "Юридический адрес: 153025, г. Иваново, Химический переулок, д. 2\nТелефон: +7 913 065-11-11",
    ]);
    $other = Page::factory()->create(['slug' => 'about', 'body' => 'Склад: ИНН 3700025101, 153025, г. Иваново']);

    legalAddressLabelMigration()->up();

    expect($privacy->fresh()->body)->toBe("1. Оператор (ИНН 3700025101, ОГРН 1243700009915, юридический адрес: 153025, г. Иваново, Химический переулок, д. 2).\n\n10. Контакты оператора\n\nООО «ОМГ АППАРЕЛЬ», ИНН 3700025101, юридический адрес: 153025, г. Иваново, Химический переулок, д. 2, email: info@svoyhod.ru.")
        ->and($consent->fresh()->body)->toBe('Я даю ООО «ОМГ АППАРЕЛЬ» (ИНН 3700025101, ОГРН 1243700009915, юридический адрес: 153025, г. Иваново, Химический переулок, д. 2) согласие.')
        ->and($requisites->fresh()->body)->toBe($requisites->body)
        ->and($other->fresh()->body)->toBe($other->body);

    legalAddressLabelMigration()->down();

    expect($privacy->fresh()->body)->toBe($privacy->body)
        ->and($consent->fresh()->body)->toBe($consent->body);
});

test('seeded pages show the office address in contacts and the labelled legal address on the legal pages', function () {
    $this->seed([HomePageContentSeeder::class, LegalPagesSeeder::class]);

    $this->get('/')
        ->assertOk()
        ->assertSee(MOSCOW_OFFICE_ADDRESS)
        ->assertDontSee('Котельники')
        ->assertDontSee('Иваново');

    $this->get(route('legal.requisites'))
        ->assertOk()
        ->assertSee('Юридический адрес: 153025, г. Иваново');

    foreach (['legal.privacy', 'legal.consent'] as $route) {
        $body = Page::query()->where('slug', str($route)->after('legal.'))->value('body');

        expect(substr_count($body, '153025, г. Иваново'))
            ->toBe(substr_count($body, 'юридический адрес: 153025, г. Иваново'));

        $this->get(route($route))
            ->assertOk()
            ->assertSee('юридический адрес: 153025, г. Иваново')
            ->assertSee(MOSCOW_OFFICE_ADDRESS);
    }
});
