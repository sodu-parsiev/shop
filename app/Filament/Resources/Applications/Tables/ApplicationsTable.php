<?php

namespace App\Filament\Resources\Applications\Tables;

use App\Enums\ApplicationStatus;
use App\Filament\Exports\ApplicationExporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer_name')
                    ->searchable(),
                TextColumn::make('company')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('assignedManager.name')
                    ->label('Assigned manager')
                    ->placeholder('Unassigned'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(fn () => collect(ApplicationStatus::cases())
                        ->mapWithKeys(fn (ApplicationStatus $status) => [$status->value => $status->label()])),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->exporter(ApplicationExporter::class)
                    ->visible(fn () => auth()->user()?->can('export_application') ?? false),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(ApplicationExporter::class)
                        ->visible(fn () => auth()->user()?->can('export_application') ?? false),
                ]),
            ]);
    }
}
