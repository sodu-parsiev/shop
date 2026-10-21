<?php

use App\Enums\ProductStatus;
use App\Http\Middleware\Services\StorefrontRedirectService;
use App\Models\Catalog\Product;
use App\Models\Content\Page;
use App\Models\Content\Redirect;
use Illuminate\Http\Request;

test('published legal pages render with metadata and schema', function () {
    Page::factory()->create([
        'slug' => 'privacy',
        'name' => 'Политика',
        'title' => 'Политика конфиденциальности',
        'body' => "Первый абзац.\n\nВторой абзац.",
        'page_type' => 'legal',
        'is_published' => true,
        'meta_title' => 'Privacy Meta',
        'canonical_url' => '/privacy',
    ]);

    $response = $this->get(route('legal.privacy'));

    $response->assertOk();
    $response->assertSee('<title>Privacy Meta</title>', false);
    $response->assertSee('Политика конфиденциальности');
    $response->assertSee('Первый абзац.');
    $response->assertSee('WebPage', false);
    $response->assertSee('BreadcrumbList', false);
});

test('unpublished legal pages return not found', function () {
    Page::factory()->create([
        'slug' => 'privacy',
        'is_published' => false,
    ]);

    $this->get(route('legal.privacy'))->assertNotFound();
});

test('sitemap includes home active products and published legal pages only', function () {
    $active = Product::factory()->create([
        'slug' => 'active-tee',
        'status' => ProductStatus::Active,
    ]);
    $inactive = Product::factory()->create([
        'slug' => 'inactive-tee',
        'status' => ProductStatus::Inactive,
    ]);
    Page::factory()->create(['slug' => 'privacy', 'is_published' => true]);
    Page::factory()->create(['slug' => 'size-guide', 'is_published' => true]);
    Page::factory()->create(['slug' => 'consent', 'is_published' => false]);

    $response = $this->get(route('sitemap'));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/xml');
    $response->assertSee('<loc>'.url('/').'/</loc>', false);
    $response->assertSee($active->publicUrl(), false);
    $response->assertSee(url('/privacy'), false);
    $response->assertSee(url('/size-guide'), false);
    $response->assertDontSee($inactive->publicUrl(), false);
    $response->assertDontSee(url('/consent'), false);
});

test('the sitemap dates the homepage by its latest content change, not the crawl time', function () {
    $this->travelTo(now()->subDays(3));
    Product::factory()->create(['status' => ProductStatus::Active]);
    $this->travelBack();

    $this->get(route('sitemap'))
        ->assertSee('<lastmod>'.now()->subDays(3)->startOfSecond()->toAtomString().'</lastmod>', false)
        ->assertDontSee('<lastmod>'.now()->startOfSecond()->toAtomString().'</lastmod>', false);
});

test('robots route exposes an absolute sitemap url and keeps crawlers out of the admin', function () {
    $this->get(route('robots'))
        ->assertOk()
        ->assertSee('User-agent: *')
        ->assertSee("Disallow: /admin\nDisallow: /livewire\nDisallow: /up\n", false)
        ->assertSee('Clean-param: utm_source&utm_medium&utm_campaign&utm_content&utm_term&yclid&ysclid&gclid&fbclid', false)
        ->assertSee('Sitemap: '.url('/sitemap.xml'), false);
});

test('no static robots.txt shadows the robots route', function () {
    // nginx serves files from public/ before falling through to Laravel.
    expect(public_path('robots.txt'))->not->toBeFile();
});

// Built by hand: the HTTP test client strips trailing slashes before the request reaches the middleware.
test('trailing slashes redirect to the canonical url and keep the query string', function (string $uri, string $target) {
    $response = app(StorefrontRedirectService::class)->redirectFor(Request::create($uri));

    expect($response?->getStatusCode())->toBe(301)
        ->and($response->getTargetUrl())->toBe(url($target));
})->with([
    'product' => ['/catalog/shopper/', '/catalog/shopper'],
    'with query' => ['/catalog/shopper/?utm_source=x', '/catalog/shopper?utm_source=x'],
    'legal page' => ['/privacy/', '/privacy'],
]);

test('catalog urls with uppercase letters redirect to the lowercase slug', function () {
    $this->get('/catalog/Shopper')->assertRedirect('/catalog/shopper')->assertStatus(301);
});

test('the homepage, canonical paths and non-get requests are left alone', function (string $method, string $uri) {
    expect(app(StorefrontRedirectService::class)->redirectFor(Request::create($uri, $method)))->toBeNull();
})->with([
    'homepage' => ['GET', '/'],
    'canonical product' => ['GET', '/catalog/shopper'],
    'upload with uppercase ulid' => ['GET', '/storage/products/covers/01JABCDEF.webp'],
    'order post' => ['POST', '/orders/'],
]);

test('active redirects apply to get requests and count hits', function () {
    $redirect = Redirect::factory()->create([
        'source_path' => '/old-catalog',
        'target_url' => '/catalog',
        'status_code' => 301,
    ]);

    $this->get('/old-catalog?utm_source=test')
        ->assertRedirect('/catalog?utm_source=test');

    expect($redirect->refresh()->hits)->toBe(1);
    expect($redirect->last_used_at)->not->toBeNull();
});

test('redirect loops are ignored', function () {
    Redirect::factory()->create([
        'source_path' => '/loop',
        'target_url' => '/loop',
    ]);

    $this->get('/loop')->assertNotFound();
});
