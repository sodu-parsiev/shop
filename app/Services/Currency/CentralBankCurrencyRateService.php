<?php

namespace App\Services\Currency;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use Throwable;

class CentralBankCurrencyRateService
{
    public const USD_RUB_CACHE_KEY = 'currency.usd_rub_rate';

    public function usdRubRate(): ?float
    {
        $rate = Cache::get(self::USD_RUB_CACHE_KEY);

        return $this->normalizeRate($rate);
    }

    public function refreshUsdRubRate(): ?float
    {
        $url = (string) config('services.central_bank.daily_rates_url');

        try {
            $response = Http::timeout((int) config('services.central_bank.http_timeout', 5))
                ->get($url);

            if ($response->failed()) {
                Log::warning('CBR USD/RUB rate refresh failed', [
                    'status' => $response->status(),
                    'url' => $url,
                ]);

                return null;
            }

            $rate = $this->parseUsdRubRate($response->body());

            if ($rate === null) {
                Log::warning('CBR USD/RUB rate response did not contain a valid USD rate', [
                    'url' => $url,
                ]);

                return null;
            }

            Cache::put(
                self::USD_RUB_CACHE_KEY,
                $rate,
                now()->addSeconds((int) config('services.central_bank.rate_cache_ttl', 86400)),
            );

            return $rate;
        } catch (Throwable $e) {
            Log::warning('CBR USD/RUB rate refresh threw', [
                'url' => $url,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function parseUsdRubRate(string $xml): ?float
    {
        try {
            $document = new SimpleXMLElement($xml);
        } catch (Throwable) {
            return null;
        }

        foreach ($document->Valute as $valute) {
            if ((string) $valute->CharCode !== 'USD') {
                continue;
            }

            $unitRate = $this->parseDecimal((string) $valute->VunitRate);

            if ($unitRate !== null && $unitRate > 0.0) {
                return round($unitRate, 4);
            }

            $nominal = $this->parseDecimal((string) $valute->Nominal);
            $value = $this->parseDecimal((string) $valute->Value);

            if ($nominal === null || $nominal <= 0.0 || $value === null || $value <= 0.0) {
                return null;
            }

            return round($value / $nominal, 4);
        }

        return null;
    }

    private function parseDecimal(string $value): ?float
    {
        $normalized = str_replace(',', '.', trim($value));

        if (! is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }

    private function normalizeRate(mixed $rate): ?float
    {
        if (! is_numeric($rate)) {
            return null;
        }

        $rate = (float) $rate;

        return $rate > 0.0 ? $rate : null;
    }
}
