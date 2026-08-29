<?php

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Models\Catalog\Color;
use App\Models\Catalog\Density;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductPriceTier;
use App\Models\Catalog\Size;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
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
        'volume' => '5000',
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
    ProductPriceTier::factory()->create([
        'product_id' => $product->id,
        'quantity' => 5000,
        'unit_price' => 170,
        'currency' => 'RUB',
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
    expect($order->message)->toContain('5 000 шт.');
    expect($order->message)->toContain('Нужна консультация по маркировке.');
    expect($order->lines)->toHaveCount(1);
    expect($order->lines->first()->product_id)->toBe($product->id);
    expect($order->lines->first()->product_name)->toBe('Базовая футболка — белая');
    expect($order->lines->first()->quantity)->toBe(5000);
    expect($order->lines->first()->product_moq)->toBe(5000);
    expect($order->lines->first()->unit_price)->toBe('170.00');
    expect($order->lines->first()->currency)->toBe('RUB');
    expect($order->lines->first()->price_quantity_tier)->toBe(5000);
    expect($order->lines->first()->price_note)->toBe('Чистый текстиль, без нанесения');
});

test('volume without a comment composes a message with just the volume label', function () {
    $product = Product::factory()->create([
        'moq' => 5000,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);

    $this->post(route('orders.store'), validOrderPayload($product, [
        'message' => null,
        'volume' => '10',
        'order_lines' => [
            [
                'product_id' => $product->id,
                'quantity' => 5000,
            ],
        ],
    ]));

    $order = Order::query()->latest('id')->first();

    expect($order->message)->toBe('10 шт.');
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

test('order lines reject active products hidden from the public catalog', function () {
    $product = Product::factory()->create([
        'moq' => 5000,
        'show_on_landing' => false,
        'status' => ProductStatus::Active,
    ]);

    $response = $this->post(route('orders.store'), validOrderPayload($product));

    $response->assertSessionHasErrors(['order_lines.0.product_id']);
    expect(Order::query()->count())->toBe(0);
});

test('order line quantity must be one of the public price tiers', function () {
    $product = Product::factory()->create([
        'moq' => 10,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);

    $response = $this->post(route('orders.store'), validOrderPayload($product, [
        'volume' => '50',
        'order_lines' => [
            [
                'product_id' => $product->id,
                'quantity' => 50,
            ],
        ],
    ]));

    $response->assertSessionHasErrors(['volume', 'order_lines.0.quantity']);
    expect(Order::query()->count())->toBe(0);
});

test('an order line for an unpriced public product stores no price snapshot', function () {
    $product = Product::factory()->create([
        'moq' => 10,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);

    $this->post(route('orders.store'), validOrderPayload($product, [
        'volume' => '10',
        'order_lines' => [
            [
                'product_id' => $product->id,
                'quantity' => 10,
            ],
        ],
    ]));

    $line = Order::query()->with('lines')->latest('id')->firstOrFail()->lines->first();

    expect($line->unit_price)->toBeNull();
    expect($line->currency)->toBeNull();
    expect($line->price_quantity_tier)->toBeNull();
    expect($line->price_note)->toBe('Цена по запросу');
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
    $color = Color::factory()->create(['name' => 'Белый']);
    $size = Size::factory()->create(['name' => 'S–2XL']);
    $density = Density::factory()->create(['name' => '240 gsm']);
    $product->colors()->attach($color);
    $product->sizes()->attach($size);
    $product->densities()->attach($density);

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

test('an order line accepts multiple attached colors selected together', function () {
    $product = Product::factory()->create([
        'moq' => 5000,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);
    $product->colors()->attach(Color::factory()->create(['name' => 'Белый']));
    $product->colors()->attach(Color::factory()->create(['name' => 'Красный']));

    $this->post(route('orders.store'), validOrderPayload($product, [
        'order_lines' => [
            ['product_id' => $product->id, 'quantity' => $product->moq, 'color' => 'Белый, Красный'],
        ],
    ]));

    $order = Order::query()->with('lines')->latest('id')->first();

    expect($order->lines->first()->preferred_color)->toBe('Белый, Красный');
});

test('order lines reject a multi-value submission if any value is not attached to the product', function () {
    $product = Product::factory()->create([
        'moq' => 5000,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);
    $product->colors()->attach(Color::factory()->create(['name' => 'Белый']));

    $response = $this->post(route('orders.store'), validOrderPayload($product, [
        'order_lines' => [
            ['product_id' => $product->id, 'quantity' => $product->moq, 'color' => 'Белый, Красный'],
        ],
    ]));

    $response->assertSessionHasErrors(['order_lines.0.color']);
    expect(Order::query()->count())->toBe(0);
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

test('order lines reject a color not attached to the product', function () {
    $product = Product::factory()->create([
        'moq' => 5000,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);
    $product->colors()->attach(Color::factory()->create(['name' => 'Белый']));

    $response = $this->post(route('orders.store'), validOrderPayload($product, [
        'order_lines' => [
            ['product_id' => $product->id, 'quantity' => $product->moq, 'color' => 'Красный'],
        ],
    ]));

    $response->assertSessionHasErrors(['order_lines.0.color']);
    expect(Order::query()->count())->toBe(0);
});

test('order lines reject a size not attached to the product', function () {
    $product = Product::factory()->create([
        'moq' => 5000,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);
    $product->sizes()->attach(Size::factory()->create(['name' => 'M']));

    $response = $this->post(route('orders.store'), validOrderPayload($product, [
        'order_lines' => [
            ['product_id' => $product->id, 'quantity' => $product->moq, 'size' => 'XL'],
        ],
    ]));

    $response->assertSessionHasErrors(['order_lines.0.size']);
    expect(Order::query()->count())->toBe(0);
});

test('order lines reject a density not attached to the product', function () {
    $product = Product::factory()->create([
        'moq' => 5000,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);
    $product->densities()->attach(Density::factory()->create(['name' => '180 gsm']));

    $response = $this->post(route('orders.store'), validOrderPayload($product, [
        'order_lines' => [
            ['product_id' => $product->id, 'quantity' => $product->moq, 'density' => '240 gsm'],
        ],
    ]));

    $response->assertSessionHasErrors(['order_lines.0.density']);
    expect(Order::query()->count())->toBe(0);
});

test('order lines reject any color value when the product has no attached colors', function () {
    $product = Product::factory()->create([
        'moq' => 5000,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);

    $response = $this->post(route('orders.store'), validOrderPayload($product, [
        'order_lines' => [
            ['product_id' => $product->id, 'quantity' => $product->moq, 'color' => 'Белый'],
        ],
    ]));

    $response->assertSessionHasErrors(['order_lines.0.color']);
    expect(Order::query()->count())->toBe(0);
});

test('order lines reject a color that is attached but deactivated', function () {
    $product = Product::factory()->create([
        'moq' => 5000,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);
    $product->colors()->attach(Color::factory()->create(['name' => 'Белый', 'is_active' => false]));

    $response = $this->post(route('orders.store'), validOrderPayload($product, [
        'order_lines' => [
            ['product_id' => $product->id, 'quantity' => $product->moq, 'color' => 'Белый'],
        ],
    ]));

    $response->assertSessionHasErrors(['order_lines.0.color']);
    expect(Order::query()->count())->toBe(0);
});

test('an empty string color preference is treated as no preference', function () {
    $product = Product::factory()->create([
        'moq' => 5000,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);
    $product->colors()->attach(Color::factory()->create(['name' => 'Белый']));

    $response = $this->post(route('orders.store'), validOrderPayload($product, [
        'order_lines' => [
            ['product_id' => $product->id, 'quantity' => $product->moq, 'color' => ''],
        ],
    ]));

    $response->assertSessionHasNoErrors();
    $order = Order::query()->with('lines')->latest('id')->first();
    expect($order->lines->first()->preferred_color)->toBeNull();
});

test('utm and source fields are persisted with the order', function () {
    $product = Product::factory()->create([
        'moq' => 5000,
        'show_on_landing' => true,
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
        'show_on_landing' => true,
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
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);

    $response = $this->post(route('orders.store'), validOrderPayload($product, [
        'website' => 'spam-link',
    ]));

    $response->assertSessionHasErrors(['website']);
    expect(Order::query()->count())->toBe(0);
});

test('a valid submission sends a formatted Telegram notification when telegram is configured', function () {
    config(['services.telegram.bot_token' => 'test-token', 'services.telegram.chat_id' => '-100200300']);
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

    $product = Product::factory()->create([
        'name' => 'Базовая футболка — белая',
        'moq' => 5000,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);
    ProductPriceTier::factory()->create([
        'product_id' => $product->id,
        'quantity' => 5000,
        'unit_price' => 170,
        'currency' => 'RUB',
    ]);

    $this->post(route('orders.store'), validOrderPayload($product));

    $order = Order::query()->latest('id')->firstOrFail();

    Http::assertSent(function ($request) use ($order) {
        return $request->url() === 'https://api.telegram.org/bottest-token/sendMessage'
            && $request['chat_id'] === '-100200300'
            && str_contains($request['text'], $order->request_number)
            && str_contains($request['text'], 'Иван Иванов')
            && str_contains($request['text'], '+7 999 123-45-67')
            && str_contains($request['text'], 'Базовая футболка — белая')
            && str_contains($request['text'], '850 000 ₽');
    });
});

test('a duplicate submission token does not send a second Telegram notification', function () {
    config(['services.telegram.bot_token' => 'test-token', 'services.telegram.chat_id' => '-100200300']);
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

    $product = Product::factory()->create([
        'moq' => 5000,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);
    $payload = validOrderPayload($product, ['submission_token' => (string) Str::uuid()]);

    $this->post(route('orders.store'), $payload);
    $this->post(route('orders.store'), $payload);

    Http::assertSentCount(1);
});

test('no Telegram request is made when telegram is not configured', function () {
    config(['services.telegram.bot_token' => null, 'services.telegram.chat_id' => null]);
    Http::fake();

    $product = Product::factory()->create([
        'moq' => 5000,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);

    $response = $this->post(route('orders.store'), validOrderPayload($product));

    $response->assertSessionHas('orderSubmitted', true);
    Http::assertNothingSent();
});

test('order submission still succeeds when the Telegram API call fails', function () {
    config(['services.telegram.bot_token' => 'test-token', 'services.telegram.chat_id' => '-100200300']);
    Http::fake(['api.telegram.org/*' => Http::response('', 500)]);

    $product = Product::factory()->create([
        'moq' => 5000,
        'show_on_landing' => true,
        'status' => ProductStatus::Active,
    ]);

    $response = $this->post(route('orders.store'), validOrderPayload($product));

    $response->assertSessionHas('orderSubmitted', true);
    expect(Order::query()->count())->toBe(1);
});
