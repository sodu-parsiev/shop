<?php

namespace App\Filament\Resources\Catalog\Colors;

use App\Filament\Resources\Catalog\Colors\Pages\ManageColors;
use App\Filament\Support\NavigationGroups;
use App\Models\Catalog\Color;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ColorResource extends Resource
{
    protected static ?string $model = Color::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroups::catalog();
    }

    public static function getModelLabel(): string
    {
        return __('Color');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Colors');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required(),
                ColorPicker::make('hex_code')
                    ->label(__('Hex code'))
                    ->hex()
                    ->required()
                    ->regex('/^#[0-9A-Fa-f]{6}$/'),
                Toggle::make('is_active')
                    ->label(__('toggles.is_active_masculine'))
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
                ColorColumn::make('hex_code')
                    ->label(__('Hex code')),
                IconColumn::make('is_active')
                    ->label(__('toggles.is_active_masculine'))
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
                    ->disabled(fn (Color $record): bool => $record->products()->exists())
                    ->tooltip(fn (Color $record): ?string => $record->products()->exists()
                        ? __('Cannot delete: still in use.')
                        : null),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (Collection $records, DeleteBulkAction $action) {
                            if ($records->contains(fn (Color $record) => $record->products()->exists())) {
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
            'index' => ManageColors::route('/'),
        ];
    }
}
