<?php

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Models\Catalog\Product;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

function validOrderPayload(Product $product, array $overrides = []): array
{
    return array_replace_recursive([
        'company' => 'ООО Ромашка',
        'customer_name' => 'Иван Иванов',
        'phone' => '+7 999 123-45-67',
        'volume' => '10000_25000',
        'message' => 'Нужна консультация по маркировке.',
        'order_lines' => [
            [
                'product_id' => $product->id,
                'quantity' => $product->moq,
            ],
        ],
    ], $overrides);
}

test('a valid submission creates an order with product lines and no email', function () {
    $product = Product::factory()->create([
        'name' => 'Базовая футболка — белая',
        'moq' => 5000,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);

    $response = $this->post(route('orders.store'), validOrderPayload($product));

    $response->assertRedirect();
    $response->assertSessionHas('orderSubmitted', true);

    $order = Order::query()->with('lines')->latest('id')->first();

    expect($order)->not->toBeNull();
    expect($order->company)->toBe('ООО Ромашка');
    expect($order->customer_name)->toBe('Иван Иванов');
    expect($order->phone)->toBe('+7 999 123-45-67');
    expect($order->status)->toBe(OrderStatus::New);
    expect($order->email)->toBeNull();
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
        'volume' => '',
    ]);

    $response->assertSessionHasErrors(['customer_name', 'phone', 'volume', 'order_lines']);
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

test('order lines only accept active products published on the landing page', function () {
    $product = Product::factory()->create([
        'moq' => 5000,
        'show_on_landing' => false,
        'status' => ProductStatus::Active,
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
            ],
        ],
    ]));

    $order = Order::query()->with('lines')->latest('id')->first();

    expect($order->lines->first()->preferred_density)->toBe('240 gsm');
    expect($order->lines->first()->preferred_size)->toBe('S–2XL');
});

test('an order line without a density or size preference stores null', function () {
    $product = Product::factory()->create([
        'moq' => 5000,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);

    $this->post(route('orders.store'), validOrderPayload($product));

    $order = Order::query()->with('lines')->latest('id')->first();

    expect($order->lines->first()->preferred_density)->toBeNull();
    expect($order->lines->first()->preferred_size)->toBeNull();
});
