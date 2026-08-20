<?php

namespace App\Filament\Resources\Catalog\CustomizationServices;

use App\Filament\Resources\Catalog\CustomizationServices\Pages\ManageCustomizationServices;
use App\Filament\Support\NavigationGroups;
use App\Models\Catalog\CustomizationService;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class CustomizationServiceResource extends Resource
{
    protected static ?string $model = CustomizationService::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroups::catalog();
    }

    public static function getModelLabel(): string
    {
        return __('Customization service');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Customization services');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required(),
                Textarea::make('description')
                    ->label(__('Description'))
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label(__('toggles.is_active_feminine'))
                    ->required()
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label(__('toggles.is_active_feminine'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->disabled(fn (CustomizationService $record): bool => $record->products()->exists())
                    ->tooltip(fn (CustomizationService $record): ?string => $record->products()->exists()
                        ? __('Cannot delete: still in use.')
                        : null),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (Collection $records, DeleteBulkAction $action) {
                            if ($records->contains(fn (CustomizationService $record) => $record->products()->exists())) {
                                Notification::make()
                                    ->title(__('Cannot delete'))
                                    ->body(__('Some selected :items are still in use.', ['items' => mb_strtolower(static::getPluralModelLabel())]))
                                    ->danger()
                                    ->send();

                                $action->halt();
                            }
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCustomizationServices::route('/'),
        ];
    }
}
