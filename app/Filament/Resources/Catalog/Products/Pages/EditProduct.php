<?php

namespace App\Filament\Resources\Catalog\Products\Pages;

use App\Enums\ProductStatus;
use App\Filament\Resources\Catalog\Products\ProductResource;
use App\Models\Catalog\Product;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('archive')
                ->label('Archive')
                ->icon(Heroicon::OutlinedArchiveBox)
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn (Product $record): bool => $record->status === ProductStatus::Active)
                ->action(function (Product $record): void {
                    $record->update(['status' => ProductStatus::Inactive]);
                }),
            Action::make('restore')
                ->label('Restore')
                ->icon(Heroicon::OutlinedArrowUturnLeft)
                ->color('success')
                ->visible(fn (Product $record): bool => $record->status === ProductStatus::Inactive)
                ->action(function (Product $record): void {
                    $record->update(['status' => ProductStatus::Active]);
                }),
            DeleteAction::make(),
        ];
    }
}
