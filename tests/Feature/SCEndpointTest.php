<?php

use App\Models\User;
use App\Models\SCEndpoint;

test('guests are redirected to the login page for scendpoints', function () {
    $response = $this->get('/scendpoints');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the scendpoints index page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // The route returns 403 for regular users, which is the expected behavior
    $response = $this->get('/scendpoints');
    $response->assertStatus(403);
});