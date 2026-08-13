<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Permission;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true),
                CheckboxList::make('permissions')
                    ->relationship('permissions', 'name')
                    ->options(fn () => Permission::query()->pluck('name', 'id'))
                    ->searchable()
                    ->bulkToggleable()
                    ->columns(3),
            ]);
    }
}
