<?php

use App\Models\Content\Page;
use Database\Seeders\LegalPagesSeeder;
use Illuminate\Database\Migrations\Migration;

function legalPagesEmailMigration(): Migration
{
    return require database_path('migrations/2026_10_21_000003_use_info_email_on_legal_pages.php');
}

test('it switches legal pages from office@ to info@ and keeps the rest of the text', function () {
    $privacy = Page::factory()->create([
        'slug' => 'privacy',
        'body' => "8. Права\n\nЗапрос на office@svoyhod.ru.\n\n10. Контакты\n\nemail: office@svoyhod.ru, телефон: +7 913 065-11-11.",
    ]);
    $guide = Page::factory()->create(['slug' => 'size-guide', 'body' => 'Как снять мерки.']);
    $guideUpdatedAt = $guide->updated_at;

    $this->travel(1)->minute();
    legalPagesEmailMigration()->up();

    expect($privacy->fresh()->body)->toBe("8. Права\n\nЗапрос на info@svoyhod.ru.\n\n10. Контакты\n\nemail: info@svoyhod.ru, телефон: +7 913 065-11-11.")
        ->and($privacy->fresh()->updated_at->gt($privacy->updated_at))->toBeTrue()
        ->and($guide->fresh()->body)->toBe('Как снять мерки.')
        ->and($guide->fresh()->updated_at->eq($guideUpdatedAt))->toBeTrue();
});

test('rolling back restores office@ on the seeded legal pages only', function () {
    $consent = Page::factory()->create(['slug' => 'consent', 'body' => 'Отзыв: office@svoyhod.ru']);
    $other = Page::factory()->create(['slug' => 'about', 'body' => 'Пишите на info@svoyhod.ru']);

    legalPagesEmailMigration()->up();
    legalPagesEmailMigration()->down();

    expect($consent->fresh()->body)->toBe('Отзыв: office@svoyhod.ru')
        ->and($other->fresh()->body)->toBe('Пишите на info@svoyhod.ru');
});

test('seeded legal pages only mention info@ and the privacy policy routes data requests there', function () {
    $this->seed(LegalPagesSeeder::class);

    expect(Page::query()->where('body', 'like', '%office@%')->exists())->toBeFalse();

    $this->get(route('legal.privacy'))
        ->assertOk()
        ->assertSee('направив запрос на info@svoyhod.ru')
        ->assertDontSee('office@svoyhod.ru');
});
