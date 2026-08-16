<?php

use App\Filament\Resources\Catalog\CustomizationServices\Pages\ManageCustomizationServices;
use App\Models\Catalog\CustomizationService;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Administrator');
    $this->actingAs($admin);
});

test('it can list customization services', function () {
    $services = CustomizationService::factory()->count(3)->create();

    Livewire::test(ManageCustomizationServices::class)
        ->assertCanSeeTableRecords($services);
});

test('it can create a customization service', function () {
    Livewire::test(ManageCustomizationServices::class)
        ->callAction('create', data: [
            'name' => 'Embroidery',
            'description' => 'Textured logo and text application for premium lines.',
            'is_active' => true,
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('customization_services', [
        'name' => 'Embroidery',
        'description' => 'Textured logo and text application for premium lines.',
        'is_active' => true,
    ]);
});

test('it can update a customization service', function () {
    $service = CustomizationService::factory()->create();

    Livewire::test(ManageCustomizationServices::class)
        ->callTableAction('edit', record: $service, data: [
            'name' => 'Renamed',
            'is_active' => false,
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('customization_services', [
        'id' => $service->id,
        'name' => 'Renamed',
        'is_active' => false,
    ]);
});

test('it validates that name is required when creating a customization service', function () {
    Livewire::test(ManageCustomizationServices::class)
        ->callAction('create', data: [
            'name' => '',
        ])
        ->assertHasActionErrors(['name' => 'required']);
});
