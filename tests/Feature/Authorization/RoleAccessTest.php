<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function actingAsRole(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

dataset('content resources', [
    'products' => ['/admin/catalog/products'],
    'categories' => ['/admin/catalog/categories'],
    'colors' => ['/admin/catalog/colors'],
    'sizes' => ['/admin/catalog/sizes'],
    'densities' => ['/admin/catalog/densities'],
    'customization services' => ['/admin/catalog/customization-services'],
    'pages' => ['/admin/content/pages'],
    'faqs' => ['/admin/content/faqs'],
]);

dataset('administration resources', [
    'users' => ['/admin/users'],
    'roles' => ['/admin/roles'],
]);

const APPLICATIONS_URL = '/admin/applications';

// Administrator: full access.

test('administrator can access every content resource', function (string $url) {
    $user = actingAsRole('Administrator');

    $this->actingAs($user)->get($url)->assertOk();
})->with('content resources');

test('administrator can access applications', function () {
    $user = actingAsRole('Administrator');

    $this->actingAs($user)->get(APPLICATIONS_URL)->assertOk();
});

test('administrator can access administration resources', function (string $url) {
    $user = actingAsRole('Administrator');

    $this->actingAs($user)->get($url)->assertOk();
})->with('administration resources');

// Content Manager: content only, no applications, no user/role administration.

test('content manager can access every content resource', function (string $url) {
    $user = actingAsRole('Content Manager');

    $this->actingAs($user)->get($url)->assertOk();
})->with('content resources');

test('content manager cannot access applications', function () {
    $user = actingAsRole('Content Manager');

    $this->actingAs($user)->get(APPLICATIONS_URL)->assertForbidden();
});

test('content manager cannot access administration resources', function (string $url) {
    $user = actingAsRole('Content Manager');

    $this->actingAs($user)->get($url)->assertForbidden();
})->with('administration resources');

// Sales Manager: applications only, no content editing, no user/role administration.

test('sales manager can access applications', function () {
    $user = actingAsRole('Sales Manager');

    $this->actingAs($user)->get(APPLICATIONS_URL)->assertOk();
});

test('sales manager cannot access content resources', function (string $url) {
    $user = actingAsRole('Sales Manager');

    $this->actingAs($user)->get($url)->assertForbidden();
})->with('content resources');

test('sales manager cannot access administration resources', function (string $url) {
    $user = actingAsRole('Sales Manager');

    $this->actingAs($user)->get($url)->assertForbidden();
})->with('administration resources');

// Users without any admin role cannot reach the panel at all.

test('a user with no role cannot access the admin panel', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin')->assertForbidden();
});
