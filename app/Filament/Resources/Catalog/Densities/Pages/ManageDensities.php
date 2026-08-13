<?php

namespace App\Filament\Resources\Catalog\Densities\Pages;

use App\Filament\Resources\Catalog\Densities\DensityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDensities extends ManageRecords
{
    protected static string $resource = DensityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
