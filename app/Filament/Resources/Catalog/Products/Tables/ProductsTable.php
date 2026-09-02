<?php

namespace App\Filament\Resources\Catalog\Products\Tables;

use App\Enums\AvailabilityStatus;
use App\Enums\ProductStatus;
use App\Models\Catalog\Product;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('priceTiers'))
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                TextColumn::make('sku')
                    ->label(__('SKU'))
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label(__('Category'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),
                TextColumn::make('availability_status')
                    ->label(__('Availability'))
                    ->badge(),
                TextColumn::make('starting_price')
                    ->label(__('From price').' (USD)')
                    ->state(fn (Product $record): string => $record->startingStoredPriceLabel()),
                IconColumn::make('featured')
                    ->label(__('Featured'))
                    ->boolean(),
                IconColumn::make('show_on_landing')
                    ->label(__('Show on landing page'))
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
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(collect(ProductStatus::cases())->mapWithKeys(fn (ProductStatus $status) => [$status->value => $status->label()])),
                SelectFilter::make('availability_status')
                    ->label(__('Availability'))
                    ->options(collect(AvailabilityStatus::cases())->mapWithKeys(fn (AvailabilityStatus $status) => [$status->value => $status->label()])),
                TernaryFilter::make('featured')
                    ->label(__('Featured')),
                TernaryFilter::make('show_on_landing')
                    ->label(__('Show on landing page')),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->recordActions([
                Action::make('archive')
                    ->label(__('Archive'))
                    ->icon(Heroicon::OutlinedArchiveBox)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Product $record): bool => $record->status === ProductStatus::Active)
                    ->action(fn (Product $record) => $record->update(['status' => ProductStatus::Inactive])),
                Action::make('restore')
                    ->label(__('Restore'))
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->color('success')
                    ->visible(fn (Product $record): bool => $record->status === ProductStatus::Inactive)
                    ->action(fn (Product $record) => $record->update(['status' => ProductStatus::Active])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('archive')
                        ->label(__('Archive selected'))
                        ->icon(Heroicon::OutlinedArchiveBox)
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['status' => ProductStatus::Inactive]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
