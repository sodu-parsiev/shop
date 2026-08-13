<?php

namespace App\Filament\Resources\Catalog\Densities;

use App\Filament\Resources\Catalog\Densities\Pages\ManageDensities;
use App\Models\Catalog\Density;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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

class DensityResource extends Resource
{
    protected static ?string $model = Density::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Catalog';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('gsm')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->suffix('gsm'),
                Toggle::make('is_active')
                    ->required()
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('gsm')
                    ->suffix(' gsm')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
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
                    ->disabled(fn (Density $record): bool => $record->variants()->exists())
                    ->tooltip(fn (Density $record): ?string => $record->variants()->exists()
                        ? 'Cannot delete: still used by one or more product variants.'
                        : null),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (Collection $records, DeleteBulkAction $action) {
                            if ($records->contains(fn (Density $record) => $record->variants()->exists())) {
                                Notification::make()
                                    ->title('Cannot delete densities')
                                    ->body('One or more selected densities are still used by product variants.')
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
            'index' => ManageDensities::route('/'),
        ];
    }
}
