<?php

test('the order returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
