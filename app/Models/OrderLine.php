<?php

namespace App\Models;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductPriceTier;
use Database\Factories\OrderLineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'product_id',
    'product_name',
    'category_name',
    'availability_label',
    'quantity',
    'product_moq',
    'unit_price',
    'currency',
    'price_quantity_tier',
    'price_note',
    'preferred_density',
    'preferred_size',
    'preferred_color',
])]
class OrderLine extends Model
{
    /** @use HasFactory<OrderLineFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'product_moq' => 'integer',
            'unit_price' => 'decimal:2',
            'price_quantity_tier' => 'integer',
        ];
    }

    public function formattedUnitPrice(): ?string
    {
        if ($this->unit_price === null || $this->currency === null) {
            return null;
        }

        return ProductPriceTier::formatUnitPrice($this->unit_price, $this->currency);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
