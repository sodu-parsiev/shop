<?php

namespace App\Filament\Resources\Catalog\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('customizationServices')
                    ->relationship('customizationServices', 'name')
                    ->multiple()
                    ->preload(),
            ]);
    }
}
