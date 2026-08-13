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

test('a category can be deactivated via the edit form', function () {
    $category = Category::factory()->create(['is_active' => true]);

    Livewire::test(ManageCategories::class)
        ->callTableAction('edit', record: $category, data: [
            'name' => $category->name,
            'is_active' => false,
        ])
        ->assertHasNoTableActionErrors();

    expect($category->refresh()->is_active)->toBeFalse();
});

test('a category can be reactivated via the edit form', function () {
    $category = Category::factory()->create(['is_active' => false]);

    Livewire::test(ManageCategories::class)
        ->callTableAction('edit', record: $category, data: [
            'name' => $category->name,
            'is_active' => true,
        ])
        ->assertHasNoTableActionErrors();

    expect($category->refresh()->is_active)->toBeTrue();
});

test('the is_active icon column reflects the active state', function () {
    $active = Category::factory()->create(['is_active' => true]);
    $inactive = Category::factory()->create(['is_active' => false]);

    Livewire::test(ManageCategories::class)
        ->assertTableColumnStateSet('is_active', true, record: $active)
        ->assertTableColumnStateSet('is_active', false, record: $inactive);
});
