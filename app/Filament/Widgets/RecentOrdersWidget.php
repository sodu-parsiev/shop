<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentOrdersWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->can('view_any_order') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Recent orders'))
            ->query(Order::query()->latest()->limit(5))
            ->paginated(false)
            ->columns([
                TextColumn::make('customer_name')
                    ->label(__('Customer name')),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),
                TextColumn::make('assignedManager.name')
                    ->label(__('Assigned manager'))
                    ->placeholder(__('Unassigned')),
                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime(),
            ])
            ->recordUrl(fn (Order $record): string => OrderResource::getUrl('edit', ['record' => $record]))
            ->emptyStateHeading(__('No orders yet'));
    }
}
