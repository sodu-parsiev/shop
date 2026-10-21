<?php

use App\Filament\Pages\ManageIntegrations;
use App\Models\Settings\IntegrationSettings;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Notifications\Notification;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

const WEBHOOK_URL = 'https://example.bitrix24.ru/rest/1/secret-token/';

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function actingAsAdministrator(): User
{
    $admin = User::factory()->create();
    $admin->assignRole('Administrator');
    test()->actingAs($admin);

    return $admin;
}

test('the migration creates the manage_integrations permission without running seeders', function () {
    Permission::query()->where('name', 'manage_integrations')->delete();

    (require database_path('migrations/2026_10_21_000001_add_manage_integrations_permission.php'))->up();

    expect(Permission::query()->where('name', 'manage_integrations')->exists())->toBeTrue();
});

test('administrator can open the integrations page', function () {
    actingAsAdministrator();

    $this->get(ManageIntegrations::getUrl())
        ->assertOk()
        ->assertSee('wire:click="checkBitrix24Connection"', false);
});

test('managers without the permission cannot open the integrations page', function (string $role) {
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)->get(ManageIntegrations::getUrl())->assertForbidden();
})->with(['Content Manager', 'Sales Manager']);

test('a role granted manage_integrations can open the integrations page', function () {
    $user = User::factory()->create();
    $user->assignRole('Sales Manager');
    $user->givePermissionTo('manage_integrations');

    $this->actingAs($user)->get(ManageIntegrations::getUrl())->assertOk();
});

test('it loads saved settings into the form', function () {
    actingAsAdministrator();
    IntegrationSettings::create(['bitrix24_enabled' => true, 'bitrix24_webhook_url' => WEBHOOK_URL]);

    Livewire::test(ManageIntegrations::class)
        ->assertFormSet([
            'bitrix24_enabled' => true,
            'bitrix24_webhook_url' => WEBHOOK_URL,
        ]);
});

test('saving stores the webhook url encrypted', function () {
    actingAsAdministrator();

    Livewire::test(ManageIntegrations::class)
        ->fillForm(['bitrix24_enabled' => true, 'bitrix24_webhook_url' => WEBHOOK_URL])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = IntegrationSettings::current();

    expect($settings->bitrix24_enabled)->toBeTrue()
        ->and($settings->bitrix24WebhookUrl())->toBe(rtrim(WEBHOOK_URL, '/'))
        ->and(DB::table('integration_settings')->value('bitrix24_webhook_url'))->not->toContain('secret-token');
});

test('the webhook url is required when sending is switched on', function () {
    actingAsAdministrator();

    Livewire::test(ManageIntegrations::class)
        ->fillForm(['bitrix24_enabled' => true, 'bitrix24_webhook_url' => null])
        ->call('save')
        ->assertHasFormErrors(['bitrix24_webhook_url' => 'required']);
});

test('sending can be switched off without a webhook url', function () {
    actingAsAdministrator();
    IntegrationSettings::create(['bitrix24_enabled' => true, 'bitrix24_webhook_url' => WEBHOOK_URL]);

    Livewire::test(ManageIntegrations::class)
        ->fillForm(['bitrix24_enabled' => false, 'bitrix24_webhook_url' => null])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(IntegrationSettings::current()->bitrix24WebhookUrl())->toBeNull();
});

test('the webhook url must look like a bitrix24 rest webhook', function (string $url) {
    actingAsAdministrator();

    Livewire::test(ManageIntegrations::class)
        ->fillForm(['bitrix24_enabled' => true, 'bitrix24_webhook_url' => $url])
        ->call('save')
        ->assertHasFormErrors(['bitrix24_webhook_url' => 'regex']);
})->with([
    'plain http' => ['http://example.bitrix24.ru/rest/1/secret-token/'],
    'missing token' => ['https://example.bitrix24.ru/rest/1/'],
    'not a webhook' => ['https://example.bitrix24.ru/crm/lead/'],
]);

test('connection check reports success when the webhook can read crm leads', function () {
    actingAsAdministrator();
    Http::fake(['example.bitrix24.ru/*' => Http::response(['result' => ['ID' => ['type' => 'integer']]])]);

    Livewire::test(ManageIntegrations::class)
        ->fillForm(['bitrix24_webhook_url' => WEBHOOK_URL])
        ->call('checkBitrix24Connection')
        ->assertNotified('Подключение работает');

    Http::assertSent(fn ($request) => $request->url() === 'https://example.bitrix24.ru/rest/1/secret-token/crm.lead.fields.json');
});

test('connection check explains bitrix24 errors', function (array $error, string $message) {
    actingAsAdministrator();
    Http::fake(['example.bitrix24.ru/*' => Http::response($error, 401)]);

    Livewire::test(ManageIntegrations::class)
        ->fillForm(['bitrix24_webhook_url' => WEBHOOK_URL])
        ->call('checkBitrix24Connection')
        ->assertNotified(
            Notification::make()
                ->title('Подключение не работает')
                ->body($message)
                ->danger()
        );
})->with([
    'missing crm scope' => [
        ['error' => 'insufficient_scope', 'error_description' => 'The request requires higher privileges'],
        'У вебхука нет доступа к CRM. Включите право «CRM (crm)» в настройках вебхука.',
    ],
    'wrong token' => [
        ['error' => 'INVALID_CREDENTIALS', 'error_description' => 'Invalid request credentials'],
        'Битрикс24 отклонил вебхук: неверный токен или ID пользователя.',
    ],
]);

test('connection check reports an unreachable portal', function () {
    actingAsAdministrator();
    Http::fake(fn () => throw new ConnectionException('Could not resolve host'));

    Livewire::test(ManageIntegrations::class)
        ->fillForm(['bitrix24_webhook_url' => WEBHOOK_URL])
        ->call('checkBitrix24Connection')
        ->assertNotified('Подключение не работает');
});

test('connection check asks for a url when the field is empty', function () {
    actingAsAdministrator();
    Http::fake();

    Livewire::test(ManageIntegrations::class)
        ->call('checkBitrix24Connection')
        ->assertNotified('Укажите URL вебхука');

    Http::assertNothingSent();
});
