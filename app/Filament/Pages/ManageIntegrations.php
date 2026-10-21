<?php

namespace App\Filament\Pages;

use App\Filament\Support\NavigationGroups;
use App\Models\Settings\IntegrationSettings;
use App\Services\Bitrix24\Bitrix24ConnectionChecker;
use App\Services\Bitrix24\Bitrix24ConnectionException;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageIntegrations extends Page
{
    private const BITRIX24_WEBHOOK_PATTERN = '#^https://[^/\s]+/rest/\d+/[^/\s]+/?$#';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;

    protected static ?string $navigationLabel = 'Интеграции';

    protected static ?string $title = 'Интеграции';

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroups::administration();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage_integrations') ?? false;
    }

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $settings = IntegrationSettings::current();

        $this->form->fill([
            'bitrix24_enabled' => $settings->bitrix24_enabled,
            'bitrix24_webhook_url' => $settings->bitrix24_webhook_url,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Битрикс24')
                    ->description('Каждая новая заявка с сайта создаётся лидом в CRM Битрикс24.')
                    ->schema([
                        Toggle::make('bitrix24_enabled')
                            ->label('Отправлять заявки в Битрикс24'),
                        TextInput::make('bitrix24_webhook_url')
                            ->label('URL входящего вебхука')
                            ->password()
                            ->revealable()
                            ->required(fn (Get $get): bool => (bool) $get('bitrix24_enabled'))
                            ->regex(self::BITRIX24_WEBHOOK_PATTERN)
                            ->validationMessages([
                                'required' => 'Укажите URL вебхука, чтобы включить отправку.',
                                'regex' => 'Ожидается адрес вида https://портал.bitrix24.ru/rest/1/abc123/',
                            ])
                            ->helperText('Битрикс24 → Разработчикам → Другое → Входящий вебхук. Нужно право «CRM (crm)». Адрес содержит секретный токен — не передавайте его третьим лицам.'),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Сохранить')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                            Action::make('checkBitrix24')
                                ->label('Проверить подключение')
                                ->color('gray')
                                ->action('checkBitrix24Connection'),
                        ])->key('form-actions'),
                    ]),
            ]);
    }

    public function save(): void
    {
        IntegrationSettings::current()->update($this->form->getState());

        Notification::make()
            ->title('Сохранено')
            ->success()
            ->send();
    }

    /**
     * Checks the URL currently in the form, so it can be tried before saving.
     */
    public function checkBitrix24Connection(Bitrix24ConnectionChecker $checker): void
    {
        $webhookUrl = $this->form->getState()['bitrix24_webhook_url'] ?? null;

        if (blank($webhookUrl)) {
            Notification::make()
                ->title('Укажите URL вебхука')
                ->warning()
                ->send();

            return;
        }

        try {
            $checker->check($webhookUrl);
        } catch (Bitrix24ConnectionException $e) {
            Notification::make()
                ->title('Подключение не работает')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Подключение работает')
            ->body('Вебхук принят, доступ к CRM есть.')
            ->success()
            ->send();
    }
}
