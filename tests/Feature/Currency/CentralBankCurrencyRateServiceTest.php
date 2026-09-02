<?php

use App\Services\Currency\CentralBankCurrencyRateService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

test('it refreshes and caches the USD RUB rate from Bank of Russia XML', function () {
    Http::fake([
        'https://www.cbr.ru/scripts/XML_daily.asp' => Http::response(
            '<?xml version="1.0" encoding="windows-1251"?><ValCurs><Valute ID="R01235"><CharCode>USD</CharCode><Nominal>1</Nominal><Value>92,1234</Value></Valute></ValCurs>',
            200,
        ),
    ]);

    $rate = app(CentralBankCurrencyRateService::class)->refreshUsdRubRate();

    expect($rate)->toBe(92.1234)
        ->and(Cache::get(CentralBankCurrencyRateService::USD_RUB_CACHE_KEY))->toBe(92.1234);
});

test('it returns no USD RUB rate when there is no cached rate', function () {
    Cache::forget(CentralBankCurrencyRateService::USD_RUB_CACHE_KEY);

    expect(app(CentralBankCurrencyRateService::class)->usdRubRate())->toBeNull();
});

test('it does not cache a failed Bank of Russia response', function () {
    Http::fake([
        'https://www.cbr.ru/scripts/XML_daily.asp' => Http::response('', 500),
    ]);

    $rate = app(CentralBankCurrencyRateService::class)->refreshUsdRubRate();

    expect($rate)->toBeNull()
        ->and(Cache::has(CentralBankCurrencyRateService::USD_RUB_CACHE_KEY))->toBeFalse();
});
