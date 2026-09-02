<?php

namespace App\Filament\Resources\Catalog\Products\Schemas;

use App\Enums\AvailabilityStatus;
use App\Enums\ProductStatus;
use App\Models\Catalog\Density;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductPriceTier;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Product')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make(__('General'))
                            ->icon(Heroicon::OutlinedInformationCircle)
                            ->components([
                                TextInput::make('name')
                                    ->label(__('Name'))
                                    ->required()
                                    ->columnSpan(1),
                                TextInput::make('h1')
                                    ->label(__('H1'))
                                    ->helperText(__('Optional public product page H1. Falls back to the product name.'))
                                    ->columnSpan(1),
                                TextInput::make('slug')
                                    ->label(__('Slug'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visible(fn (?Product $record): bool => $record !== null)
                                    ->columnSpan(1),
                                TextInput::make('sku')
                                    ->label(__('SKU'))
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->columnSpan(1),
                                Select::make('category_id')
                                    ->label(__('Category'))
                                    ->relationship('category', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(1),
                                Textarea::make('short_description')
                                    ->label(__('Short description'))
                                    ->helperText(__('Shown on catalog cards and used as SEO fallback.'))
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Textarea::make('description')
                                    ->label(__('Description'))
                                    ->columnSpanFull(),
                                Textarea::make('composition')
                                    ->label(__('Composition')),
                                TextInput::make('fit')
                                    ->label(__('Fit')),
                                Repeater::make('size_table')
                                    ->label(__('Size table'))
                                    ->schema([
                                        TextInput::make('size')
                                            ->label(__('Size'))
                                            ->required(),
                                        TextInput::make('chest')
                                            ->label(__('Chest'))
                                            ->required(),
                                        TextInput::make('length')
                                            ->label(__('Length'))
                                            ->required(),
                                        TextInput::make('sleeve')
                                            ->label(__('Sleeve'))
                                            ->required(),
                                    ])
                                    ->columns(4)
                                    ->defaultItems(0)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                        Tab::make(__('Variants'))
                            ->icon(Heroicon::OutlinedSwatch)
                            ->components([
                                Select::make('customizationServices')
                                    ->label(__('Customization services'))
                                    ->relationship('customizationServices', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->columnSpanFull(),
                                Section::make(__('Colors, sizes & densities'))
                                    ->description(__('The product will be offered in every selected color, size, and density.'))
                                    ->components([
                                        CheckboxList::make('colors')
                                            ->label(__('Colors'))
                                            ->relationship(
                                                name: 'colors',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                                            )
                                            ->bulkToggleable()
                                            ->searchable()
                                            ->columns(3),
                                        CheckboxList::make('sizes')
                                            ->label(__('Sizes'))
                                            ->relationship(
                                                name: 'sizes',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                                            )
                                            ->bulkToggleable()
                                            ->searchable()
                                            ->columns(3),
                                        CheckboxList::make('densities')
                                            ->label(__('Densities'))
                                            ->relationship(
                                                name: 'densities',
                                                modifyQueryUsing: fn ($query) => $query->where('is_active', true)->orderBy('gsm'),
                                            )
                                            ->getOptionLabelFromRecordUsing(fn (Density $record): string => "{$record->name} ({$record->gsm} г/м²)")
                                            ->bulkToggleable()
                                            ->searchable()
                                            ->columns(3),
                                    ]),
                            ]),
                        Tab::make(__('Inventory'))
                            ->icon(Heroicon::OutlinedArchiveBox)
                            ->components([
                                TextInput::make('moq')
                                    ->label(__('MOQ'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(5000)
                                    ->required(),
                                Select::make('availability_status')
                                    ->label(__('Availability'))
                                    ->options(collect(AvailabilityStatus::cases())->mapWithKeys(fn (AvailabilityStatus $status) => [$status->value => $status->label()]))
                                    ->default(AvailabilityStatus::MadeToOrder)
                                    ->required()
                                    ->live(),
                                TextInput::make('stock_quantity')
                                    ->label(__('Stock quantity'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->visible(fn (Get $get): bool => $get('availability_status') === AvailabilityStatus::InStock->value)
                                    ->dehydratedWhenHidden()
                                    ->dehydrateStateUsing(fn ($state, Get $get) => $get('availability_status') === AvailabilityStatus::InStock->value ? $state : null),
                                Textarea::make('stock_conditions')
                                    ->label(__('Stock / conditions'))
                                    ->columnSpanFull(),
                                Repeater::make('priceTiers')
                                    ->label(__('Price tiers').' (USD)')
                                    ->relationship('priceTiers')
                                    ->orderColumn('sort_order')
                                    ->reorderable()
                                    ->defaultItems(0)
                                    ->addActionLabel(__('Add price tier'))
                                    ->schema([
                                        TextInput::make('quantity')
                                            ->label(__('Quantity'))
                                            ->numeric()
                                            ->minValue(1)
                                            ->required(),
                                        TextInput::make('unit_price')
                                            ->label(__('Unit price').' (USD)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->required(),
                                        Select::make('currency')
                                            ->label(__('Currency'))
                                            ->options([
                                                ProductPriceTier::DEFAULT_CURRENCY => ProductPriceTier::DEFAULT_CURRENCY,
                                            ])
                                            ->default(ProductPriceTier::DEFAULT_CURRENCY)
                                            ->required(),
                                    ])
                                    ->columns(3)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                        Tab::make(__('Media'))
                            ->icon(Heroicon::OutlinedPhoto)
                            ->components([
                                FileUpload::make('cover_image')
                                    ->label(__('Cover image'))
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
                                    ->addActionLabel(__('Add gallery image'))
                                    ->collapsible()
                                    ->schema([
                                        FileUpload::make('path')
                                            ->label(__('Image'))
                                            ->image()
                                            ->required()
                                            ->disk('public')
                                            ->directory('products/gallery')
                                            ->visibility('public'),
                                        TextInput::make('alt_text')
                                            ->label(__('Alt text')),
                                    ]),
                            ]),
                        Tab::make(__('Publishing'))
                            ->icon(Heroicon::OutlinedGlobeAlt)
                            ->components([
                                Select::make('status')
                                    ->label(__('Status'))
                                    ->options(collect(ProductStatus::cases())->mapWithKeys(fn (ProductStatus $status) => [$status->value => $status->label()]))
                                    ->default(ProductStatus::Active)
                                    ->required(),
                                Toggle::make('featured')
                                    ->label(__('Featured'))
                                    ->default(false),
                                Toggle::make('show_on_landing')
                                    ->label(__('Show on landing page'))
                                    ->default(false),
                            ])
                            ->columns(3),
                        Tab::make(__('SEO'))
                            ->icon(Heroicon::OutlinedMagnifyingGlass)
                            ->components([
                                TextInput::make('meta_title')
                                    ->label(__('Meta title'))
                                    ->columnSpanFull(),
                                Textarea::make('meta_description')
                                    ->label(__('Meta description'))
                                    ->columnSpanFull(),
                                TextInput::make('canonical_url')
                                    ->label(__('Canonical URL'))
                                    ->url(),
                                TextInput::make('og_image')
                                    ->label(__('OG image')),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }
}
