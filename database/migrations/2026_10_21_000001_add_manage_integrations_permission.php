<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Deploys don't run seeders, so the permission is created here to make it
 * assignable to roles on existing databases.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Permission::findOrCreate('manage_integrations');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::query()->where('name', 'manage_integrations')->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
