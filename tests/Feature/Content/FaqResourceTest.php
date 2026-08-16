<?php

use App\Filament\Resources\Content\Faqs\Pages\ManageFaqs;
use App\Models\Content\Faq;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Administrator');
    $this->actingAs($admin);
});

test('it can list faqs', function () {
    $faqs = Faq::factory()->count(3)->create();

    Livewire::test(ManageFaqs::class)
        ->assertCanSeeTableRecords($faqs);
});

test('it can create a faq with a question and answer', function () {
    Livewire::test(ManageFaqs::class)
        ->callAction('create', data: [
            'question' => 'Какой минимальный заказ?',
            'answer' => 'Минимальная производственная партия — 5 000 изделий.',
            'is_active' => true,
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('faqs', [
        'question' => 'Какой минимальный заказ?',
        'answer' => 'Минимальная производственная партия — 5 000 изделий.',
        'is_active' => true,
    ]);
});

test('it can update a faq', function () {
    $faq = Faq::factory()->create();

    Livewire::test(ManageFaqs::class)
        ->callTableAction('edit', record: $faq, data: [
            'question' => 'Обновлённый вопрос?',
            'answer' => $faq->answer,
            'is_active' => false,
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('faqs', [
        'id' => $faq->id,
        'question' => 'Обновлённый вопрос?',
        'is_active' => false,
    ]);
});

test('it validates that question and answer are required when creating a faq', function () {
    Livewire::test(ManageFaqs::class)
        ->callAction('create', data: [
            'question' => '',
            'answer' => '',
        ])
        ->assertHasActionErrors(['question' => 'required', 'answer' => 'required']);
});
