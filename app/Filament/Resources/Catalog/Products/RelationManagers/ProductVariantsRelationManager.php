<?php

namespace App\Filament\Resources\Catalog\Products\RelationManagers;

use App\Enums\AvailabilityStatus;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

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
                    ->preload()
                    ->unique(
                        table: 'product_variants',
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule
                            ->where('product_id', $this->getOwnerRecord()->id)
                            ->where('size_id', $get('size_id'))
                            ->where('density_id', $get('density_id')),
                    ),
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
                Select::make('availability_status')
                    ->options(collect(AvailabilityStatus::cases())->mapWithKeys(fn (AvailabilityStatus $status) => [$status->value => $status->label()]))
                    ->default(AvailabilityStatus::MadeToOrder)
                    ->required(),
                TextInput::make('stock_quantity')
                    ->numeric()
                    ->minValue(0),
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
                TextColumn::make('availability_status')
                    ->badge(),
                TextColumn::make('stock_quantity'),
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
