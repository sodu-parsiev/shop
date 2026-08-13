<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Seeds a single local Administrator account so the panel is reachable
     * after a fresh setup. Does nothing outside local/testing — production
     * admins are created via `php artisan make:filament-user`.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $user = User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
            [
                'name' => 'Administrator',
                'password' => env('ADMIN_PASSWORD', 'password'),
            ]
        );

        $user->syncRoles(['Administrator']);
    }
}
