<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Catalog\Products\ProductResource;
use App\Models\Catalog\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentProductsWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->can('view_any_product') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Recent products'))
            ->query(Product::query()->latest()->limit(5))
            ->paginated(false)
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name')),
                TextColumn::make('category.name')
                    ->label(__('Category')),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),
                TextColumn::make('availability_status')
                    ->label(__('Availability'))
                    ->badge(),
            ])
            ->recordUrl(fn (Product $record): string => ProductResource::getUrl('edit', ['record' => $record]))
            ->emptyStateHeading(__('No products yet'));
    }
}
