<?php

namespace App\Services;

use App\Enums\ContactMethod;
use App\Models\Order;
use App\Models\OrderLine;
use App\Services\Currency\PriceFormatter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TelegramOrderNotifier
{
    public function __construct(private readonly PriceFormatter $priceFormatter) {}

    public function notifyOrderCreated(Order $order): void
    {
        $botToken = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (! $botToken || ! $chatId) {
            return;
        }

        try {
            $response = Http::timeout(5)
                ->retry(2, 200)
                ->asForm()
                ->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $this->buildMessage($order),
                ]);

            if ($response->failed()) {
                Log::warning('Telegram order notification failed', [
                    'order_id' => $order->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (Throwable $e) {
            Log::warning('Telegram order notification threw', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function buildMessage(Order $order): string
    {
        if (! $order->relationLoaded('lines')) {
            $order->load('lines');
        }

        $contactMethod = ContactMethod::tryFrom((string) $order->preferred_contact_method)?->label()
            ?? $order->preferred_contact_method;

        $header = collect([
            "🔔 Новая заявка {$order->request_number}",
            '',
            "Имя: {$order->customer_name}",
            "Телефон: {$order->phone}",
            'Компания: '.($order->company ?: '—'),
            "Связь: {$contactMethod}",
        ])->implode("\n");

        $lines = $order->lines
            ->values()
            ->map(fn (OrderLine $line, int $index): string => $this->formatLine($line, $index + 1))
            ->implode("\n\n");

        $page = $order->source_url ?? $order->landing_url ?? $order->referrer_url ?? '—';

        $footer = collect([
            "Итого: {$this->formatTotal($order->lines)}",
            '',
            'Date: '.$order->created_at->format('d.m.Y'),
            'Time: '.$order->created_at->format('H:i'),
            "Page: {$page}",
        ])->implode("\n");

        return implode("\n\n", array_filter([$header, $lines, $footer]));
    }

    private function formatLine(OrderLine $line, int $number): string
    {
        $details = collect([
            $line->preferred_density ? "   Плотность: {$line->preferred_density}" : null,
            $line->preferred_size ? "   Размер: {$line->preferred_size}" : null,
            $line->preferred_color ? "   Цвет: {$line->preferred_color}" : null,
            '   Количество: '.number_format($line->quantity, 0, ',', ' '),
            '   Цена: '.$this->linePrice($line),
        ])->filter()->implode("\n");

        return "{$number}. {$line->product_name}\n{$details}";
    }

    private function linePrice(OrderLine $line): string
    {
        if ($line->unit_price === null || $line->currency === null) {
            return 'по запросу';
        }

        return $this->priceFormatter->formatLineTotal($line->unit_price, $line->quantity, $line->currency)
            ?? 'по запросу';
    }

    /**
     * @param  Collection<int, OrderLine>  $lines
     */
    private function formatTotal(Collection $lines): string
    {
        $totals = $lines
            ->map(fn (OrderLine $line): ?float => $line->unit_price !== null && $line->currency !== null
                ? $this->priceFormatter->lineTotalAmount($line->unit_price, $line->quantity, $line->currency)
                : null)
            ->filter(fn (?float $amount): bool => $amount !== null);

        if ($totals->isEmpty()) {
            return 'цена по запросу';
        }

        $sum = $this->priceFormatter->formatRubAmount($totals->sum());

        return $totals->count() < $lines->count()
            ? "{$sum} + позиции с ценой по запросу"
            : $sum;
    }
}
