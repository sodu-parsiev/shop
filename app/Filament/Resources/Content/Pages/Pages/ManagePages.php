<?php

namespace App\Filament\Resources\Content\Pages\Pages;

use App\Filament\Resources\Content\Pages\PageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePages extends ManageRecords
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
