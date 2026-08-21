<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Filament\Exports\OrderExporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('request_number')
                    ->label(__('Request number'))
                    ->searchable()
                    ->sortable()
                    ->placeholder(__('Pending')),
                TextColumn::make('customer_name')
                    ->label(__('Customer name'))
                    ->searchable(),
                TextColumn::make('company')
                    ->label(__('Company'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('preferred_contact_method')
                    ->label(__('Preferred contact method'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),
                TextColumn::make('utm_source')
                    ->label(__('UTM source'))
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('from')
                            ->label(__('From')),
                        DatePicker::make('until')
                            ->label(__('Until')),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date))),
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
