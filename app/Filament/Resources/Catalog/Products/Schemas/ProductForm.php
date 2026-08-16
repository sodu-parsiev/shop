<?php

namespace App\Filament\Resources\Catalog\Products\Schemas;

use App\Enums\ProductStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->rule('alpha_dash'),
                TextInput::make('sku')
                    ->required()
                    ->unique(ignoreRecord: true),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('customizationServices')
                    ->relationship('customizationServices', 'name')
                    ->multiple()
                    ->preload(),
                Section::make('Media')
                    ->components([
                        FileUpload::make('cover_image')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('products/covers')
                            ->visibility('public'),
                        Repeater::make('images')
                            ->relationship('images')
                            ->orderColumn('sort_order')
                            ->reorderable()
                            ->defaultItems(0)
                            ->addActionLabel('Add gallery image')
                            ->collapsible()
                            ->schema([
                                FileUpload::make('path')
                                    ->label('Image')
                                    ->image()
                                    ->required()
                                    ->disk('public')
                                    ->directory('products/gallery')
                                    ->visibility('public'),
                            ]),
                    ]),
                Textarea::make('short_description'),
                Textarea::make('description'),
                Textarea::make('composition'),
                TextInput::make('fit'),
                TextInput::make('moq')
                    ->label('MOQ')
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required(),
                Textarea::make('stock_conditions')
                    ->label('Stock / conditions'),
                Select::make('status')
                    ->options(collect(ProductStatus::cases())->mapWithKeys(fn (ProductStatus $status) => [$status->value => $status->label()]))
                    ->default(ProductStatus::Active)
                    ->required(),
                Toggle::make('featured')
                    ->default(false),
                Toggle::make('show_on_landing')
                    ->label('Show on landing page')
                    ->default(false),
                Section::make('SEO')
                    ->components([
                        TextInput::make('meta_title'),
                        Textarea::make('meta_description'),
                        TextInput::make('canonical_url')
                            ->url(),
                        TextInput::make('og_image'),
                    ]),
            ]);
    }
}
