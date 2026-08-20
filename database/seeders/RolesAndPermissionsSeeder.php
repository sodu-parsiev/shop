<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Models that get the standard CRUD permission set
     * (view_any, view, create, update, delete, delete_any).
     */
    private const CRUD_SLUGS = [
        'product',
        'category',
        'color',
        'size',
        'density',
        'customization_service',
        'page',
        'faq',
        'order',
        'user',
        'role',
    ];

    /**
     * Content resources Content Manager has full CRUD access to.
     */
    private const CONTENT_SLUGS = [
        'product',
        'category',
        'color',
        'size',
        'density',
        'customization_service',
        'page',
        'faq',
    ];

    public function run(): void
    {
        foreach (self::CRUD_SLUGS as $slug) {
            foreach (['view_any', 'view', 'create', 'update', 'delete', 'delete_any'] as $ability) {
                Permission::findOrCreate("{$ability}_{$slug}");
            }
        }

        Permission::findOrCreate('export_order');
        Permission::findOrCreate('manage_media');
        Permission::findOrCreate('manage_seo');

        // Permissions were just created above; the registrar's cache must be
        // flushed before syncPermissions() below resolves them by name,
        // otherwise it throws PermissionDoesNotExist on a fresh database.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Administrator has full access via a Gate::before bypass (see
        // AppServiceProvider), so it intentionally holds no explicit
        // permissions here — toggling permissions for this role in the
        // admin UI has no effect.
        Role::findOrCreate('Administrator');

        $contentManagerPermissions = collect(self::CONTENT_SLUGS)
            ->flatMap(fn (string $slug) => collect(['view_any', 'view', 'create', 'update', 'delete', 'delete_any'])
                ->map(fn (string $ability) => "{$ability}_{$slug}"))
            ->push('manage_media')
            ->push('manage_seo')
            ->all();

        Role::findOrCreate('Content Manager')
            ->syncPermissions($contentManagerPermissions);

        Role::findOrCreate('Sales Manager')
            ->syncPermissions([
                'view_any_order',
                'view_order',
                'update_order',
                'export_order',
            ]);
    }
}
