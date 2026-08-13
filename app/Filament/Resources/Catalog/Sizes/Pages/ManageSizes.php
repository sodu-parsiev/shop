<?php

namespace App\Filament\Resources\Catalog\Sizes\Pages;

use App\Filament\Resources\Catalog\Sizes\SizeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSizes extends ManageRecords
{
    protected static string $resource = SizeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
