<?php

namespace App\Filament\Exports;

use App\Models\Order;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class OrderExporter extends Exporter
{
    protected static ?string $model = Order::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('request_number')
                ->label(__('Request number')),
            ExportColumn::make('customer_name')
                ->label(__('Customer name')),
            ExportColumn::make('company')
                ->label(__('Company')),
            ExportColumn::make('email')
                ->label(__('Email')),
            ExportColumn::make('phone')
                ->label(__('Phone')),
            ExportColumn::make('preferred_contact_method')
                ->label(__('Preferred contact method')),
            ExportColumn::make('status')
                ->label(__('Status')),
            ExportColumn::make('line_summary')
                ->label(__('Order lines')),
            ExportColumn::make('assignedManager.name')
                ->label(__('Assigned manager')),
            ExportColumn::make('utm_source')
                ->label(__('UTM source')),
            ExportColumn::make('utm_medium')
                ->label(__('UTM medium')),
            ExportColumn::make('utm_campaign')
                ->label(__('UTM campaign')),
            ExportColumn::make('source_url')
                ->label(__('Source URL')),
            ExportColumn::make('referrer_url')
                ->label(__('Referrer URL')),
            ExportColumn::make('created_at')
                ->label(__('Created at')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $successfulRowsCount = $export->successful_rows;
        $body = trans_choice('orders.export_completed', $successfulRowsCount, ['count' => $successfulRowsCount]);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.trans_choice('orders.export_failed', $failedRowsCount, ['count' => $failedRowsCount]);
        }

        return $body;
    }
}
