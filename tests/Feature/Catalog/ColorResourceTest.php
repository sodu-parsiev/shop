<?php

use App\Filament\Resources\Catalog\Colors\Pages\ManageColors;
use App\Models\Catalog\Color;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Administrator');
    $this->actingAs($admin);
});

test('it can list colors', function () {
    $colors = Color::factory()->count(3)->create();

    Livewire::test(ManageColors::class)
        ->assertCanSeeTableRecords($colors);
});

test('it can create a color', function () {
    Livewire::test(ManageColors::class)
        ->callAction('create', data: [
            'name' => 'Crimson',
            'hex_code' => '#DC143C',
            'is_active' => true,
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('colors', [
        'name' => 'Crimson',
        'hex_code' => '#DC143C',
        'is_active' => true,
    ]);
});

test('it can update a color', function () {
    $color = Color::factory()->create();

    Livewire::test(ManageColors::class)
        ->callTableAction('edit', record: $color, data: [
            'name' => 'Renamed',
            'hex_code' => '#00FF00',
            'is_active' => false,
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('colors', [
        'id' => $color->id,
        'name' => 'Renamed',
        'hex_code' => '#00FF00',
        'is_active' => false,
    ]);
});

test('it validates that hex_code must be a valid hex color', function () {
    Livewire::test(ManageColors::class)
        ->callAction('create', data: [
            'name' => 'Bad Color',
            'hex_code' => 'not-a-color',
        ])
        ->assertHasActionErrors(['hex_code' => 'regex']);
});

test('it validates that name is required when creating a color', function () {
    Livewire::test(ManageColors::class)
        ->callAction('create', data: [
            'name' => '',
            'hex_code' => '#123456',
        ])
        ->assertHasActionErrors(['name' => 'required']);
});
