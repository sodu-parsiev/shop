<?php

namespace App\Http\Requests;

use App\Enums\ProductStatus;
use App\Models\Catalog\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'volume' => ['required', 'in:5000_10000,10000_25000,25000_plus'],
            'message' => ['nullable', 'string', 'max:5000'],
            'order_lines' => ['required', 'array', 'min:1', 'max:20'],
            'order_lines.*' => ['array'],
            'order_lines.*.product_id' => ['required', 'integer', 'distinct'],
            'order_lines.*.quantity' => ['required', 'integer', 'min:1'],
            'order_lines.*.density' => ['nullable', 'string', 'max:255'],
            'order_lines.*.size' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $lines = $this->input('order_lines', []);

                if (! is_array($lines) || $lines === []) {
                    return;
                }

                $products = Product::query()
                    ->whereIn('id', collect($lines)->pluck('product_id')->filter()->all())
                    ->where('status', ProductStatus::Active)
                    ->where('show_on_landing', true)
                    ->get()
                    ->keyBy('id');

                foreach ($lines as $index => $line) {
                    if (! is_array($line)) {
                        continue;
                    }

                    $productId = $line['product_id'] ?? null;
                    $quantity = (int) ($line['quantity'] ?? 0);
                    $product = $products->get($productId);

                    if (! $product) {
                        $validator->errors()->add("order_lines.{$index}.product_id", 'Выберите товар из каталога.');

                        continue;
                    }

                    if ($quantity < $product->moq) {
                        $validator->errors()->add(
                            "order_lines.{$index}.quantity",
                            sprintf('Минимальный объём для этого товара — %s шт.', number_format($product->moq, 0, ',', ' '))
                        );
                    }
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_name.required' => 'Укажите контактное лицо.',
            'phone.required' => 'Укажите телефон.',
            'volume.required' => 'Выберите объём партии.',
            'volume.in' => 'Выберите объём партии из предложенных вариантов.',
            'order_lines.required' => 'Добавьте хотя бы один товар в заявку.',
            'order_lines.min' => 'Добавьте хотя бы один товар в заявку.',
            'order_lines.*.product_id.distinct' => 'Товар уже добавлен в заявку.',
        ];
    }
}
