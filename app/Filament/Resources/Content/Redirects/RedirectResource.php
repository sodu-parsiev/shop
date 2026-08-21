<?php

namespace App\Filament\Resources\Content\Redirects;

use App\Filament\Resources\Content\Redirects\Pages\ManageRedirects;
use App\Filament\Support\NavigationGroups;
use App\Models\Content\Redirect;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroups::content();
    }

    public static function getModelLabel(): string
    {
        return __('Redirect');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Redirects');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('source_path')
                    ->label(__('Source path'))
                    ->helperText(__('Use a local path such as /old-page. GET and HEAD requests only.'))
                    ->required()
                    ->startsWith('/')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('target_url')
                    ->label(__('Target URL'))
                    ->helperText(__('Use a local path or full URL. Self-redirect loops are ignored.'))
                    ->required()
                    ->maxLength(255),
                Select::make('status_code')
                    ->label(__('Status code'))
                    ->options([
                        301 => '301',
                        302 => '302',
                    ])
                    ->default(301)
                    ->required(),
                Toggle::make('is_active')
                    ->label(__('Active'))
                    ->default(true),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('source_path')
                    ->label(__('Source path'))
                    ->searchable(),
                TextColumn::make('target_url')
                    ->label(__('Target URL'))
                    ->searchable()
                    ->wrap(),
                TextColumn::make('status_code')
                    ->label(__('Status'))
                    ->badge(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
                TextColumn::make('hits')
                    ->label(__('Hits'))
                    ->sortable(),
                TextColumn::make('last_used_at')
                    ->label(__('Last used at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('Active')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRedirects::route('/'),
        ];
    }
}
