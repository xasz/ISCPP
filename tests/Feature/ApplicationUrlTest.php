<?php

use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Testing\TestResponse;

/**
 * Simulates a request that reached the app through a reverse proxy which passed an
 * internal Host header on.
 */
function requestThroughProxy(): TestResponse
{
    return test()->actingAs(User::factory()->create())
        ->get('http://localhost:8880/sctenantgroups')
        ->assertOk();
}

test('production builds urls from APP_URL instead of the proxied host header', function () {
    config()->set('app.url', 'https://oithalo.example.local:8833');
    app()->detectEnvironment(fn () => 'production');
    (new AppServiceProvider(app()))->forceApplicationUrl();

    requestThroughProxy()
        ->assertSee('https://oithalo.example.local:8833/sctenantgroups/create', false)
        ->assertDontSee('localhost:8880', false);
});

test('outside production urls still follow the request', function () {
    config()->set('app.url', 'https://oithalo.example.local:8833');
    (new AppServiceProvider(app()))->forceApplicationUrl();

    requestThroughProxy()
        ->assertSee('http://localhost:8880/sctenantgroups/create', false);
});
