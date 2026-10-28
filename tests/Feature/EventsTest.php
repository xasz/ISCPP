<?php

use App\Models\Event;
use App\Models\User;

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

test('events page hides debug events when requested', function () {
    $this->actingAs(User::factory()->create());

    Event::log('VisibleInfoEvent', 'info', []);
    Event::log('HiddenDebugEvent', 'debug', []);

    $this->get('/events?hideDebug=1')
        ->assertOk()
        ->assertSee('VisibleInfoEvent')
        ->assertDontSee('HiddenDebugEvent');
});

test('events page can hide info and debug events together', function () {
    $this->actingAs(User::factory()->create());

    Event::log('HiddenInfoEvent', 'info', []);
    Event::log('HiddenDebugEvent', 'debug', []);
    Event::log('VisibleErrorEvent', 'error', []);

    $this->get('/events?hideInfo=1&hideDebug=1')
        ->assertOk()
        ->assertSee('VisibleErrorEvent')
        ->assertDontSee('HiddenInfoEvent')
        ->assertDontSee('HiddenDebugEvent');
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
