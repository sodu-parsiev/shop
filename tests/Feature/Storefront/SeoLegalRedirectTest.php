<?php

use App\Enums\ProductStatus;
use App\Models\Catalog\Product;
use App\Models\Content\Page;
use App\Models\Content\Redirect;

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
    Page::factory()->create(['slug' => 'consent', 'is_published' => false]);

    $response = $this->get(route('sitemap'));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/xml');
    $response->assertSee(url('/'), false);
    $response->assertSee($active->publicUrl(), false);
    $response->assertSee(url('/privacy'), false);
    $response->assertDontSee($inactive->publicUrl(), false);
    $response->assertDontSee(url('/consent'), false);
});

test('robots route exposes the sitemap', function () {
    $this->get(route('robots'))
        ->assertOk()
        ->assertSee('User-agent: *')
        ->assertSee(route('sitemap'));
});

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
