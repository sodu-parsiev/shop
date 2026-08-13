<?php

namespace App\Filament\Exports;

use App\Models\Application;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class ApplicationExporter extends Exporter
{
    protected static ?string $model = Application::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('customer_name'),
            ExportColumn::make('company'),
            ExportColumn::make('email'),
            ExportColumn::make('phone'),
            ExportColumn::make('status'),
            ExportColumn::make('assignedManager.name')
                ->label('Assigned manager'),
            ExportColumn::make('created_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your application export has completed and '.Str::of('row')->counted($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Str::of('row')->counted($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
