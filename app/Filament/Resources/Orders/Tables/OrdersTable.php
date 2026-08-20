<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Filament\Exports\OrderExporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer_name')
                    ->label(__('Customer name'))
                    ->searchable(),
                TextColumn::make('company')
                    ->label(__('Company'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),
                TextColumn::make('assignedManager.name')
                    ->label(__('Assigned manager'))
                    ->placeholder(__('Unassigned')),
                TextColumn::make('line_summary')
                    ->label(__('Order lines'))
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(fn () => collect(OrderStatus::cases())
                        ->mapWithKeys(fn (OrderStatus $status) => [$status->value => $status->label()])),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->exporter(OrderExporter::class)
                    ->visible(fn () => auth()->user()?->can('export_order') ?? false),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(OrderExporter::class)
                        ->visible(fn () => auth()->user()?->can('export_order') ?? false),
                ]),
            ]);
    }
}
