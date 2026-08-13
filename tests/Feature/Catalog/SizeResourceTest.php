<?php

use App\Filament\Resources\Catalog\Sizes\Pages\ManageSizes;
use App\Models\Catalog\Size;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Administrator');
    $this->actingAs($admin);
});

test('it can list sizes', function () {
    $sizes = Size::factory()->count(3)->create();

    Livewire::test(ManageSizes::class)
        ->assertCanSeeTableRecords($sizes);
});

test('it can create a size', function () {
    Livewire::test(ManageSizes::class)
        ->callAction('create', data: [
            'name' => 'XXL',
            'is_active' => true,
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('sizes', [
        'name' => 'XXL',
        'is_active' => true,
    ]);
});

test('it can update a size', function () {
    $size = Size::factory()->create();

    Livewire::test(ManageSizes::class)
        ->callTableAction('edit', record: $size, data: [
            'name' => 'Renamed',
            'is_active' => false,
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('sizes', [
        'id' => $size->id,
        'name' => 'Renamed',
        'is_active' => false,
    ]);
});

test('it validates that name is required when creating a size', function () {
    Livewire::test(ManageSizes::class)
        ->callAction('create', data: [
            'name' => '',
        ])
        ->assertHasActionErrors(['name' => 'required']);
});
