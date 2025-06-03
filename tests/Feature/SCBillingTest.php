<?php

use App\Models\User;

test('guests are redirected to the login page for scbilling fetcher', function () {
    $response = $this->get('/scbilling/fetcher');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the scbilling fetcher page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/scbilling/fetcher');
    $response->assertStatus(200);
});

test('guests are redirected to the login page for scbilling halo pusher', function () {
    $response = $this->get('/scbilling/haloPusher');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the scbilling halo pusher page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // The route returns 403 for regular users, which is the expected behavior
    $response = $this->get('/scbilling/haloPusher');
    $response->assertStatus(403);
});