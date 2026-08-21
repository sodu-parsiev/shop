<?php

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Models\Catalog\Product;
use App\Models\Order;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

function validOrderPayload(Product $product, array $overrides = []): array
{
    return array_replace_recursive([
        'company' => 'ООО Ромашка',
        'customer_name' => 'Иван Иванов',
        'phone' => '+7 999 123-45-67',
        'email' => 'buyer@example.com',
        'preferred_contact_method' => 'phone',
        'volume' => '10000_25000',
        'message' => 'Нужна консультация по маркировке.',
        'consent' => '1',
        'submission_token' => (string) Str::uuid(),
        'order_lines' => [
            [
                'product_id' => $product->id,
                'quantity' => $product->moq,
            ],
        ],
    ], $overrides);
}

test('a valid submission creates an order with request number, contact fields, and product lines', function () {
    $product = Product::factory()->create([
        'name' => 'Базовая футболка — белая',
        'moq' => 5000,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);

    $response = $this->post(route('orders.store'), validOrderPayload($product));

    $response->assertRedirect();
    $response->assertSessionHas('orderSubmitted', true);
    $response->assertSessionHas('orderRequestNumber');

    $order = Order::query()->with('lines')->latest('id')->first();

    expect($order)->not->toBeNull();
    expect($order->company)->toBe('ООО Ромашка');
    expect($order->customer_name)->toBe('Иван Иванов');
    expect($order->phone)->toBe('+7 999 123-45-67');
    expect($order->email)->toBe('buyer@example.com');
    expect($order->preferred_contact_method)->toBe('phone');
    expect($order->status)->toBe(OrderStatus::New);
    expect($order->request_number)->toStartWith('SH-');
    expect($order->consent_accepted_at)->not->toBeNull();
    expect($order->submission_token)->not->toBeNull();
    expect($order->message)->toContain('10 000–25 000 шт.');
    expect($order->message)->toContain('Нужна консультация по маркировке.');
    expect($order->lines)->toHaveCount(1);
    expect($order->lines->first()->product_id)->toBe($product->id);
    expect($order->lines->first()->product_name)->toBe('Базовая футболка — белая');
    expect($order->lines->first()->quantity)->toBe(5000);
    expect($order->lines->first()->product_moq)->toBe(5000);
});

test('volume without a comment composes a message with just the volume label', function () {
    $product = Product::factory()->create([
        'moq' => 5000,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);

    $this->post(route('orders.store'), validOrderPayload($product, [
        'message' => null,
        'volume' => '5000_10000',
    ]));

    $order = Order::query()->latest('id')->first();

    expect($order->message)->toBe('5 000–10 000 шт.');
});

test('missing required fields fail validation and create no order', function () {
    $response = $this->post(route('orders.store'), [
        'customer_name' => '',
        'phone' => '',
        'email' => '',
        'preferred_contact_method' => '',
        'volume' => '',
        'consent' => '',
        'submission_token' => '',
    ]);

    $response->assertSessionHasErrors(['customer_name', 'phone', 'email', 'preferred_contact_method', 'volume', 'consent', 'submission_token', 'order_lines']);
    expect(Order::query()->count())->toBe(0);
});

test('an invalid volume choice fails validation', function () {
    $product = Product::factory()->create([
        'moq' => 5000,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);

    $response = $this->post(route('orders.store'), validOrderPayload($product, [
        'volume' => 'not_a_real_option',
    ]));

    $response->assertSessionHasErrors(['volume']);
});

test('order line quantity must meet the selected product moq', function () {
    $product = Product::factory()->create([
        'moq' => 10000,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);

    $response = $this->post(route('orders.store'), validOrderPayload($product, [
        'order_lines' => [
            [
                'product_id' => $product->id,
                'quantity' => 5000,
            ],
        ],
    ]));

    $response->assertSessionHasErrors(['order_lines.0.quantity']);
    expect(Order::query()->count())->toBe(0);
});

test('order lines accept active products even when they are hidden from the landing page', function () {
    $product = Product::factory()->create([
        'moq' => 5000,
        'show_on_landing' => false,
        'status' => ProductStatus::Active,
    ]);

    $this->post(route('orders.store'), validOrderPayload($product));

    expect(Order::query()->count())->toBe(1);
});

test('order lines reject inactive products', function () {
    $product = Product::factory()->create([
        'moq' => 5000,
        'show_on_landing' => true,
        'status' => ProductStatus::Inactive,
    ]);

    $response = $this->post(route('orders.store'), validOrderPayload($product));

    $response->assertSessionHasErrors(['order_lines.0.product_id']);
    expect(Order::query()->count())->toBe(0);
});

test('a product can only be added once to the same order', function () {
    $product = Product::factory()->create([
        'moq' => 5000,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);

    $response = $this->post(route('orders.store'), validOrderPayload($product, [
        'order_lines' => [
            ['product_id' => $product->id, 'quantity' => 5000],
            ['product_id' => $product->id, 'quantity' => 5000],
        ],
    ]));

    $response->assertSessionHasErrors(['order_lines.0.product_id']);
    expect(Order::query()->count())->toBe(0);
});

test('the old applications store route is no longer registered', function () {
    expect(Route::has('applications.store'))->toBeFalse();
});

test('an order line persists the submitted density and size preference', function () {
    $product = Product::factory()->create([
        'moq' => 5000,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);

    $this->post(route('orders.store'), validOrderPayload($product, [
        'order_lines' => [
            [
                'product_id' => $product->id,
                'quantity' => $product->moq,
                'density' => '240 gsm',
                'size' => 'S–2XL',
                'color' => 'Белый',
            ],
        ],
    ]));

    $order = Order::query()->with('lines')->latest('id')->first();

    expect($order->lines->first()->preferred_density)->toBe('240 gsm');
    expect($order->lines->first()->preferred_size)->toBe('S–2XL');
    expect($order->lines->first()->preferred_color)->toBe('Белый');
});

test('an order line without color density or size preferences stores null', function () {
    $product = Product::factory()->create([
        'moq' => 5000,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);

    $this->post(route('orders.store'), validOrderPayload($product));

    $order = Order::query()->with('lines')->latest('id')->first();

    expect($order->lines->first()->preferred_density)->toBeNull();
    expect($order->lines->first()->preferred_size)->toBeNull();
    expect($order->lines->first()->preferred_color)->toBeNull();
});

test('utm and source fields are persisted with the order', function () {
    $product = Product::factory()->create([
        'moq' => 5000,
        'status' => ProductStatus::Active,
    ]);

    $this->post(route('orders.store'), validOrderPayload($product, [
        'landing_url' => 'https://shop.test/?utm_source=ya',
        'source_url' => 'https://shop.test/catalog/basic-tee-white?utm_source=ya',
        'referrer_url' => 'https://search.test/',
        'utm_source' => 'ya',
        'utm_medium' => 'cpc',
        'utm_campaign' => 'b2b',
        'utm_content' => 'banner',
        'utm_term' => 'tee',
    ]));

    $order = Order::query()->latest('id')->first();

    expect($order->landing_url)->toBe('https://shop.test/?utm_source=ya');
    expect($order->source_url)->toBe('https://shop.test/catalog/basic-tee-white?utm_source=ya');
    expect($order->referrer_url)->toBe('https://search.test/');
    expect($order->utm_source)->toBe('ya');
    expect($order->utm_medium)->toBe('cpc');
    expect($order->utm_campaign)->toBe('b2b');
    expect($order->utm_content)->toBe('banner');
    expect($order->utm_term)->toBe('tee');
});

test('a duplicate submission token returns the existing order without creating another one', function () {
    $product = Product::factory()->create([
        'moq' => 5000,
        'status' => ProductStatus::Active,
    ]);
    $token = (string) Str::uuid();
    $payload = validOrderPayload($product, ['submission_token' => $token]);

    $first = $this->post(route('orders.store'), $payload);
    $requestNumber = Order::query()->firstOrFail()->request_number;
    $second = $this->post(route('orders.store'), $payload);

    $first->assertSessionHas('orderRequestNumber');
    $second->assertSessionHas('orderRequestNumber', $requestNumber);
    expect(Order::query()->count())->toBe(1);
});

test('honeypot field blocks spam submissions', function () {
    $product = Product::factory()->create([
        'moq' => 5000,
        'status' => ProductStatus::Active,
    ]);

    $response = $this->post(route('orders.store'), validOrderPayload($product, [
        'website' => 'spam-link',
    ]));

    $response->assertSessionHasErrors(['website']);
    expect(Order::query()->count())->toBe(0);
});
