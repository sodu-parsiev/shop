<?php

namespace App\Services\Currency;

class PriceFormatter
{
    public function __construct(private readonly CentralBankCurrencyRateService $rates) {}

    public function formatUnitPrice(int|float|string $amount, string $currency): ?string
    {
        $amount = $this->displayUnitAmount($amount, $currency);

        if ($amount === null) {
            return null;
        }

        return $this->formatAmount($amount).' ₽/шт';
    }

    public function formatStoredUnitPrice(int|float|string $amount, string $currency): string
    {
        return $this->formatAmount((float) $amount).' '.$this->currencySymbol($currency).'/шт';
    }

    public function formatLineTotal(int|float|string $unitPrice, int $quantity, string $currency): ?string
    {
        $unitAmount = $this->displayUnitAmount($unitPrice, $currency);

        if ($unitAmount === null) {
            return null;
        }

        return $this->formatAmount($unitAmount * $quantity).' ₽';
    }

    public function lineTotalAmount(int|float|string $unitPrice, int $quantity, string $currency): ?float
    {
        $unitAmount = $this->displayUnitAmount($unitPrice, $currency);

        return $unitAmount === null ? null : $unitAmount * $quantity;
    }

    public function formatRubAmount(int|float|string $amount): string
    {
        return $this->formatAmount((float) $amount).' ₽';
    }

    public function currencySymbol(string $currency): string
    {
        return match (strtoupper($currency)) {
            'RUB' => '₽',
            default => strtoupper($currency),
        };
    }

    private function displayUnitAmount(int|float|string $amount, string $currency): ?float
    {
        $amount = (float) $amount;

        return match (strtoupper($currency)) {
            'RUB' => $amount,
            'USD' => $this->convertUsdToRub($amount),
            default => null,
        };
    }

    private function convertUsdToRub(float $amount): ?float
    {
        $rate = $this->rates->usdRubRate();

        if ($rate === null) {
            return null;
        }

        return ceil($amount * $rate);
    }

    private function formatAmount(float $amount): string
    {
        return floor($amount) === $amount
            ? number_format($amount, 0, ',', ' ')
            : number_format($amount, 2, ',', ' ');
    }
}
