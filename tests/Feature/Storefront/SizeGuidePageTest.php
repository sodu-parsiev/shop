<?php

use App\Models\Content\Page;

test('the size guide page renders for a published page', function () {
    Page::factory()->create([
        'slug' => 'size-guide',
        'name' => 'Как определить размер',
        'title' => 'Как определить размер',
        'body' => "Первый абзац.\n\nВторой абзац.",
        'page_type' => 'content',
        'is_published' => true,
    ]);

    $response = $this->get(route('legal.size-guide'));

    $response->assertOk();
    $response->assertSee('Как определить размер');
    $response->assertSee('Первый абзац.');
});
