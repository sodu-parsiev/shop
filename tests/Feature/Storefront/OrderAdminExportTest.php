<?php

use App\Filament\Exports\OrderExporter;

test('order exporter includes request contact and attribution fields', function () {
    $columnNames = collect(OrderExporter::getColumns())
        ->map(fn ($column): string => $column->getName())
        ->all();

    expect($columnNames)->toContain(
        'request_number',
        'email',
        'phone',
        'preferred_contact_method',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'source_url',
        'referrer_url',
    );
});
