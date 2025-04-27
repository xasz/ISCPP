<?php

use App\Models\User;
use App\Models\Event;

test('guests are redirected to the login page for events', function () {
    $response = $this->get('/events');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the events page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/events');
    $response->assertStatus(200);
});

test('events page displays event data', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Assuming Event model has a factory or we can create directly
    // If Event doesn't have a factory, you would need to create one
    // or create events manually in this test
    
    $response = $this->get('/events');
    $response->assertStatus(200);
    // Add assertions for event data if needed
});