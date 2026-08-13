<?php

use App\Filament\Resources\Catalog\Categories\Pages\ManageCategories;
use App\Models\Catalog\Category;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('an administrator can reorder categories via drag-reorder', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Administrator');
    $this->actingAs($admin);

    $first = Category::factory()->create(['sort_order' => 0]);
    $second = Category::factory()->create(['sort_order' => 1]);
    $third = Category::factory()->create(['sort_order' => 2]);

    Livewire::test(ManageCategories::class)
        ->call('reorderTable', [$third->id, $first->id, $second->id]);

    expect($third->refresh()->sort_order)->toBe(1)
        ->and($first->refresh()->sort_order)->toBe(2)
        ->and($second->refresh()->sort_order)->toBe(3);
});

test('a content manager with update permission can reorder categories', function () {
    $contentManager = User::factory()->create();
    $contentManager->assignRole('Content Manager');
    $this->actingAs($contentManager);

    $first = Category::factory()->create(['sort_order' => 0]);
    $second = Category::factory()->create(['sort_order' => 1]);

    Livewire::test(ManageCategories::class)
        ->call('reorderTable', [$second->id, $first->id]);

    expect($second->refresh()->sort_order)->toBe(1)
        ->and($first->refresh()->sort_order)->toBe(2);
});

test('a user who can view but not update categories cannot reorder them', function () {
    Role::findOrCreate('Category Viewer')->syncPermissions([
        'view_any_category',
        'view_category',
    ]);

    $viewer = User::factory()->create();
    $viewer->assignRole('Category Viewer');
    $this->actingAs($viewer);

    $first = Category::factory()->create(['sort_order' => 0]);
    $second = Category::factory()->create(['sort_order' => 1]);

    Livewire::test(ManageCategories::class)
        ->call('reorderTable', [$second->id, $first->id]);

    expect($first->refresh()->sort_order)->toBe(0)
        ->and($second->refresh()->sort_order)->toBe(1);
});
