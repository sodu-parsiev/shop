<?php

use App\Filament\Pages\ManageHomePageContent;
use App\Models\Content\HomePageContent;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Administrator');
    $this->actingAs($admin);
});

test('it creates the singleton record on first visit', function () {
    expect(HomePageContent::query()->count())->toBe(0);

    Livewire::test(ManageHomePageContent::class);

    expect(HomePageContent::query()->count())->toBe(1);
});

test('it loads existing content into the form', function () {
    HomePageContent::create([
        'content' => ['hero' => ['headline_main' => 'База, на которой строятся']],
    ]);

    Livewire::test(ManageHomePageContent::class)
        ->assertFormSet([
            'hero.headline_main' => 'База, на которой строятся',
        ]);
});

test('it loads default values for missing public form labels', function () {
    HomePageContent::create([
        'content' => ['form' => ['phone' => 'ТЕЛЕФОН']],
    ]);

    Livewire::test(ManageHomePageContent::class)
        ->assertFormSet([
            'form.phone' => 'ТЕЛЕФОН',
            'form.consent' => 'Согласен на обработку персональных данных',
            'form.privacy_link' => 'Политика конфиденциальности',
            'form.consent_link' => 'Согласие на обработку',
            'footer.legal_heading' => 'ДОКУМЕНТЫ',
        ]);
});

test('saving updates the singleton content row', function () {
    HomePageContent::create(['content' => []]);

    Livewire::test(ManageHomePageContent::class)
        ->fillForm([
            'hero.headline_main' => 'Новый заголовок',
            'hero.headline_accent' => 'строятся бренды',
        ])
        ->call('save');

    $content = HomePageContent::query()->first()->content;

    expect($content['hero']['headline_main'])->toBe('Новый заголовок');
    expect($content['hero']['headline_accent'])->toBe('строятся бренды');
});
