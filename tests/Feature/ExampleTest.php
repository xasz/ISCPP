<?php

it('returns a successful response', function () {
    putenv("CACHE_STORE=file");
    $response = $this->get('/login');
    $response->assertStatus(200);
});
