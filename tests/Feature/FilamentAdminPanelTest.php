<?php

test('the filament admin login page boots', function () {
    $response = $this->get('/admin/login');

    $response->assertStatus(200);
});
