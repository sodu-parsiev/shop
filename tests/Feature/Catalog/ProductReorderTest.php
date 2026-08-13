<?php

use App\Filament\Resources\Catalog\Products\Pages\ListProducts;
use App\Models\Catalog\Product;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('an administrator can reorder products via drag-reorder', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Administrator');
    $this->actingAs($admin);

    $first = Product::factory()->create(['sort_order' => 0]);
    $second = Product::factory()->create(['sort_order' => 1]);
    $third = Product::factory()->create(['sort_order' => 2]);

    Livewire::test(ListProducts::class)
        ->call('reorderTable', [$third->id, $first->id, $second->id]);

    expect($third->refresh()->sort_order)->toBe(1)
        ->and($first->refresh()->sort_order)->toBe(2)
        ->and($second->refresh()->sort_order)->toBe(3);
});

test('a content manager with update permission can reorder products', function () {
    $contentManager = User::factory()->create();
    $contentManager->assignRole('Content Manager');
    $this->actingAs($contentManager);

    $first = Product::factory()->create(['sort_order' => 0]);
    $second = Product::factory()->create(['sort_order' => 1]);

    Livewire::test(ListProducts::class)
        ->call('reorderTable', [$second->id, $first->id]);

    expect($second->refresh()->sort_order)->toBe(1)
        ->and($first->refresh()->sort_order)->toBe(2);
});

test('a user who can view but not update products cannot reorder them', function () {
    Role::findOrCreate('Product Viewer')->syncPermissions([
        'view_any_product',
        'view_product',
    ]);

    $viewer = User::factory()->create();
    $viewer->assignRole('Product Viewer');
    $this->actingAs($viewer);

    $first = Product::factory()->create(['sort_order' => 0]);
    $second = Product::factory()->create(['sort_order' => 1]);

    Livewire::test(ListProducts::class)
        ->call('reorderTable', [$second->id, $first->id]);

    expect($first->refresh()->sort_order)->toBe(0)
        ->and($second->refresh()->sort_order)->toBe(1);
});
