<?php

use App\Services\Currency\CentralBankCurrencyRateService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('currency:refresh-usd-rub', function () {
    $rate = app(CentralBankCurrencyRateService::class)->refreshUsdRubRate();

    if ($rate === null) {
        $this->error('USD/RUB rate could not be refreshed from the Bank of Russia.');

        return 1;
    }

    $this->info('USD/RUB rate refreshed: '.number_format($rate, 4, '.', ''));

    return 0;
})->purpose('Refresh the cached Bank of Russia USD/RUB exchange rate');

Schedule::command('currency:refresh-usd-rub')->everySixHours();
