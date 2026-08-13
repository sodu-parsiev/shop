<?php

namespace App\Filament\Resources\Catalog\Products\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductVariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('color_id')
                    ->relationship('color', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('size_id')
                    ->relationship('size', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('density_id')
                    ->relationship('density', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('color.name'),
                TextColumn::make('size.name'),
                TextColumn::make('density.gsm')
                    ->suffix(' gsm'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
