<?php

namespace App\Models\Catalog;

use App\Services\Currency\PriceFormatter;
use Database\Factories\Catalog\ProductPriceTierFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_id',
    'quantity',
    'unit_price',
    'currency',
    'sort_order',
])]
class ProductPriceTier extends Model
{
    /** @use HasFactory<ProductPriceTierFactory> */
    use HasFactory;

    public const DEFAULT_CURRENCY = 'USD';

    /**
     * @var array<int, int>
     */
    private const PUBLIC_QUANTITIES = [10, 100, 500, 1000, 5000, 10000];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return array<int, int>
     */
    public static function publicQuantities(): array
    {
        return self::PUBLIC_QUANTITIES;
    }

    public static function formatUnitPrice(int|float|string $amount, string $currency): ?string
    {
        return app(PriceFormatter::class)->formatUnitPrice($amount, $currency);
    }

    public static function formatStoredUnitPrice(int|float|string $amount, string $currency): string
    {
        return app(PriceFormatter::class)->formatStoredUnitPrice($amount, $currency);
    }

    public static function currencySymbol(string $currency): string
    {
        return app(PriceFormatter::class)->currencySymbol($currency);
    }

    public function formattedUnitPrice(): ?string
    {
        return self::formatUnitPrice($this->unit_price, $this->currency);
    }

    public function formattedStoredUnitPrice(): string
    {
        return self::formatStoredUnitPrice($this->unit_price, $this->currency);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
