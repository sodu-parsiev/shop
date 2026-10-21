<?php

use Database\Seeders\HomePageContentSeeder;

test('unknown pages get the branded 404 that leads back to the catalog', function (string $uri) {
    $this->seed(HomePageContentSeeder::class);

    $this->get($uri)
        ->assertNotFound()
        ->assertSee('Такой страницы нет')
        ->assertSee('href="'.route('home').'#catalog"', false)
        ->assertSee('<meta name="robots" content="noindex, follow">', false)
        ->assertSee('<link rel="canonical" href="'.url($uri).'">', false);
})->with([
    'unknown page' => ['/no-such-page'],
    'unknown product' => ['/catalog/no-such-product'],
]);

test('the 404 page renders even before any homepage content exists', function () {
    $this->get('/no-such-page')->assertNotFound()->assertSee('Такой страницы нет');
});
