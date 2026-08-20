<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Models\Catalog\Product;
use App\Models\Order;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $activeProducts = Product::query()->where('status', ProductStatus::Active)->count();
        $newOrders = Order::query()->where('status', OrderStatus::New)->count();
        $inProgressOrders = Order::query()->where('status', OrderStatus::InProgress)->count();

        return [
            Stat::make(__('Products'), Product::query()->count())
                ->description($activeProducts.' активных')
                ->color('primary')
                ->icon(Heroicon::OutlinedRectangleStack),
            Stat::make(__('Orders'), Order::query()->count())
                ->color('primary')
                ->icon(Heroicon::OutlinedShoppingBag),
            Stat::make(__('New orders'), $newOrders)
                ->color($newOrders > 0 ? 'warning' : 'success')
                ->icon(Heroicon::OutlinedBell),
            Stat::make(__('Orders in progress'), $inProgressOrders)
                ->color('info')
                ->icon(Heroicon::OutlinedClock),
        ];
    }
}
