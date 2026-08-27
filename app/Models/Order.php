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

#[Fillable([
    'request_number',
    'customer_name',
    'company',
    'email',
    'phone',
    'preferred_contact_method',
    'message',
    'consent_accepted_at',
    'consent_ip',
    'submission_token',
    'landing_url',
    'source_url',
    'referrer_url',
    'utm_source',
    'utm_medium',
    'utm_campaign',
    'utm_content',
    'utm_term',
    'status',
    'internal_notes',
    'assigned_to',
])]
#[Appends(['line_summary'])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'consent_accepted_at' => 'datetime',
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
                '%s — %s шт. (MOQ %s)%s',
                $line->product_name,
                number_format($line->quantity, 0, ',', ' '),
                number_format($line->product_moq, 0, ',', ' '),
                $this->lineDetails($line),
            ))
            ->implode("\n");
    }

    private function lineDetails(OrderLine $line): string
    {
        $details = collect([
            $line->formattedUnitPrice(),
            $line->preferred_color,
            $line->preferred_density,
            $line->preferred_size,
        ])->filter()->implode(', ');

        return $details ? " — {$details}" : '';
    }
}
