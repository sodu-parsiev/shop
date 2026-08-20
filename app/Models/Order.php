<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['customer_name', 'company', 'email', 'phone', 'message', 'status', 'internal_notes', 'assigned_to'])]
#[Appends(['line_summary'])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
        ];
    }

    public function assignedManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }

    public function getLineSummaryAttribute(): string
    {
        if (! $this->relationLoaded('lines')) {
            $this->load('lines');
        }

        return $this->lines
            ->map(fn (OrderLine $line): string => sprintf(
                '%s — %s шт. (MOQ %s)',
                $line->product_name,
                number_format($line->quantity, 0, ',', ' '),
                number_format($line->product_moq, 0, ',', ' ')
            ))
            ->implode("\n");
    }
}
