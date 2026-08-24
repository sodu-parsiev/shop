<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Catalog\Product;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderControllerService
{
    /**
     * @var array<string, string>
     */
    private const VOLUME_LABELS = [
        '5000_10000' => '5 000–10 000 шт.',
        '10000_25000' => '10 000–25 000 шт.',
        '25000_plus' => 'Более 25 000 шт.',
    ];

    public function createFromRequest(StoreOrderRequest $request): Order
    {
        $data = $request->validated();
        $existingOrder = Order::query()
            ->where('submission_token', $data['submission_token'])
            ->first();

        if ($existingOrder) {
            return $existingOrder->load('lines');
        }

        $volumeLabel = self::VOLUME_LABELS[$data['volume']];
        $comment = $data['message'] ?? null;
        $ipAddress = $request->ip();

        $message = $comment ? "{$volumeLabel}\n\n{$comment}" : $volumeLabel;

        return DB::transaction(function () use ($data, $message, $ipAddress): Order {
            $order = Order::create([
                'customer_name' => $data['customer_name'],
                'company' => $data['company'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'],
                'preferred_contact_method' => $data['preferred_contact_method'],
                'message' => $message,
                'consent_accepted_at' => now(),
                'consent_ip' => $ipAddress,
                'submission_token' => $data['submission_token'],
                'landing_url' => $data['landing_url'] ?? null,
                'source_url' => $data['source_url'] ?? null,
                'referrer_url' => $data['referrer_url'] ?? null,
                'utm_source' => $data['utm_source'] ?? null,
                'utm_medium' => $data['utm_medium'] ?? null,
                'utm_campaign' => $data['utm_campaign'] ?? null,
                'utm_content' => $data['utm_content'] ?? null,
                'utm_term' => $data['utm_term'] ?? null,
                'status' => OrderStatus::New,
            ]);

            $order->forceFill(['request_number' => $this->requestNumber($order)])->save();

            $this->createLines($order, $data['order_lines'] ?? []);

            return $order->load('lines');
        });
    }

    /**
     * @param  array<int, array{product_id: int, quantity: int, density?: ?string, size?: ?string, color?: ?string}>  $lines
     */
    private function createLines(Order $order, array $lines): void
    {
        if ($lines === []) {
            return;
        }

        $products = Product::query()
            ->with('category')
            ->whereIn('id', collect($lines)->pluck('product_id')->all())
            ->get()
            ->keyBy('id');

        foreach ($lines as $line) {
            /** @var Product $product */
            $product = $products->get($line['product_id']);

            $order->lines()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'category_name' => $product->category?->name,
                'availability_label' => $product->isInStock() ? 'На складе' : 'Под заказ',
                'quantity' => $line['quantity'],
                'product_moq' => $product->moq,
                'preferred_density' => $this->nullableString($line['density'] ?? null),
                'preferred_size' => $this->nullableString($line['size'] ?? null),
                'preferred_color' => $this->nullableString($line['color'] ?? null),
            ]);
        }
    }

    private function nullableString(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        return $value === '' ? null : $value;
    }

    private function requestNumber(Order $order): string
    {
        return sprintf('SH-%s-%06d', $order->created_at?->format('Ymd') ?? now()->format('Ymd'), $order->id);
    }
}
