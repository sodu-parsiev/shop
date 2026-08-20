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
        $volumeLabel = self::VOLUME_LABELS[$data['volume']];
        $comment = $data['message'] ?? null;

        $message = $comment ? "{$volumeLabel}\n\n{$comment}" : $volumeLabel;

        return DB::transaction(function () use ($data, $message): Order {
            $order = Order::create([
                'customer_name' => $data['customer_name'],
                'company' => $data['company'] ?? null,
                'phone' => $data['phone'],
                'message' => $message,
                'status' => OrderStatus::New,
            ]);

            $this->createLines($order, $data['order_lines'] ?? []);

            return $order->load('lines');
        });
    }

    /**
     * @param  array<int, array{product_id: int, quantity: int, density?: ?string, size?: ?string}>  $lines
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
                'preferred_density' => $line['density'] ?? null,
                'preferred_size' => $line['size'] ?? null,
            ]);
        }
    }
}
