<?php

use App\Filament\Resources\Catalog\Categories\Pages\ManageCategories;
use App\Models\Catalog\Category;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Administrator');
    $this->actingAs($admin);
});

test('it can list categories', function () {
    $categories = Category::factory()->count(3)->create();

    Livewire::test(ManageCategories::class)
        ->assertCanSeeTableRecords($categories);
});

test('it can create a category', function () {
    Livewire::test(ManageCategories::class)
        ->callAction('create', data: [
            'name' => 'Packaging',
            'is_active' => true,
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('categories', [
        'name' => 'Packaging',
        'is_active' => true,
    ]);
});

test('it can update a category', function () {
    $category = Category::factory()->create(['name' => 'Old Name']);

    Livewire::test(ManageCategories::class)
        ->callTableAction('edit', record: $category, data: [
            'name' => 'New Name',
            'is_active' => false,
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'New Name',
        'is_active' => false,
    ]);
});

test('it validates that name is required when creating a category', function () {
    Livewire::test(ManageCategories::class)
        ->callAction('create', data: [
            'name' => '',
        ])
        ->assertHasActionErrors(['name' => 'required']);
});
