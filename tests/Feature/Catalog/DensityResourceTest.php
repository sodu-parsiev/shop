<?php

use App\Filament\Resources\Catalog\Densities\Pages\ManageDensities;
use App\Models\Catalog\Density;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Administrator');
    $this->actingAs($admin);
});

test('it can list densities', function () {
    $densities = Density::factory()->count(3)->create();

    Livewire::test(ManageDensities::class)
        ->assertCanSeeTableRecords($densities);
});

test('it can create a density', function () {
    Livewire::test(ManageDensities::class)
        ->callAction('create', data: [
            'name' => '250 gsm',
            'gsm' => 250,
            'is_active' => true,
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('densities', [
        'name' => '250 gsm',
        'gsm' => 250,
        'is_active' => true,
    ]);
});

test('it can update a density', function () {
    $density = Density::factory()->create();

    Livewire::test(ManageDensities::class)
        ->callTableAction('edit', record: $density, data: [
            'name' => 'Renamed',
            'gsm' => 300,
            'is_active' => false,
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('densities', [
        'id' => $density->id,
        'name' => 'Renamed',
        'gsm' => 300,
        'is_active' => false,
    ]);
});

test('it validates that gsm must be numeric and positive', function () {
    Livewire::test(ManageDensities::class)
        ->callAction('create', data: [
            'name' => 'Bad Density',
            'gsm' => 0,
        ])
        ->assertHasActionErrors(['gsm' => 'min']);
});

test('it validates that name is required when creating a density', function () {
    Livewire::test(ManageDensities::class)
        ->callAction('create', data: [
            'name' => '',
            'gsm' => 200,
        ])
        ->assertHasActionErrors(['name' => 'required']);
});
