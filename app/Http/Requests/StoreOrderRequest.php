<?php

namespace App\Http\Requests;

use App\Enums\ContactMethod;
use App\Enums\ProductStatus;
use App\Models\Catalog\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
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
            'email' => ['required', 'email:rfc', 'max:255'],
            'preferred_contact_method' => ['required', Rule::enum(ContactMethod::class)],
            'volume' => ['required', 'in:5000_10000,10000_25000,25000_plus'],
            'message' => ['nullable', 'string', 'max:5000'],
            'consent' => ['accepted'],
            'submission_token' => ['required', 'uuid'],
            'website' => ['prohibited'],
            'landing_url' => ['nullable', 'string', 'max:255'],
            'source_url' => ['nullable', 'string', 'max:255'],
            'referrer_url' => ['nullable', 'string', 'max:255'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'utm_content' => ['nullable', 'string', 'max:255'],
            'utm_term' => ['nullable', 'string', 'max:255'],
            'order_lines' => ['required', 'array', 'min:1', 'max:20'],
            'order_lines.*' => ['array'],
            'order_lines.*.product_id' => ['required', 'integer', 'distinct'],
            'order_lines.*.quantity' => ['required', 'integer', 'min:1'],
            'order_lines.*.density' => ['nullable', 'string', 'max:255'],
            'order_lines.*.size' => ['nullable', 'string', 'max:255'],
            'order_lines.*.color' => ['nullable', 'string', 'max:255'],
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

                if (RateLimiter::tooManyAttempts($this->rateLimitKey(), 30)) {
                    $validator->errors()->add('email', 'Слишком много заявок. Попробуйте позже.');
                }

                RateLimiter::hit($this->rateLimitKey(), 3600);

                $products = Product::query()
                    ->whereIn('id', collect($lines)->pluck('product_id')->filter()->all())
                    ->where('status', ProductStatus::Active)
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
            'email.required' => 'Укажите email.',
            'email.email' => 'Укажите корректный email.',
            'preferred_contact_method.required' => 'Выберите способ связи.',
            'volume.required' => 'Выберите объём партии.',
            'volume.in' => 'Выберите объём партии из предложенных вариантов.',
            'consent.accepted' => 'Подтвердите согласие на обработку данных.',
            'submission_token.required' => 'Обновите страницу и отправьте заявку ещё раз.',
            'submission_token.uuid' => 'Обновите страницу и отправьте заявку ещё раз.',
            'website.prohibited' => 'Заявка не прошла антиспам-проверку.',
            'order_lines.required' => 'Добавьте хотя бы один товар в заявку.',
            'order_lines.min' => 'Добавьте хотя бы один товар в заявку.',
            'order_lines.*.product_id.distinct' => 'Товар уже добавлен в заявку.',
        ];
    }

    private function rateLimitKey(): string
    {
        return implode(':', [
            'storefront-order',
            $this->ip(),
            sha1(strtolower((string) $this->input('email')).'|'.(string) $this->input('phone')),
        ]);
    }
}
