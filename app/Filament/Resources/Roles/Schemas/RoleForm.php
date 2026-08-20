<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Filament\Support\PermissionLabelTranslator;
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
                    ->label(__('Name'))
                    ->required()
                    ->unique(ignoreRecord: true),
                CheckboxList::make('permissions')
                    ->label(__('Permissions'))
                    ->relationship('permissions', 'name')
                    ->options(fn () => Permission::query()->get()
                        ->mapWithKeys(fn (Permission $permission) => [
                            $permission->id => PermissionLabelTranslator::translate($permission->name),
                        ]))
                    ->searchable()
                    ->bulkToggleable()
                    ->columns(3),
            ]);
    }
}
