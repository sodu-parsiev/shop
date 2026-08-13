<?php

namespace App\Filament\Resources\Catalog\CustomizationServices\Pages;

use App\Filament\Resources\Catalog\CustomizationServices\CustomizationServiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomizationServices extends ManageRecords
{
    protected static string $resource = CustomizationServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
