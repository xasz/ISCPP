<?php

use App\Models\User;

/**
 * Headers a reverse proxy in front of the app sets for the public address, while the
 * request itself reaches the app on an internal host.
 *
 * @return array<string, string>
 */
function forwardedHeaders(): array
{
    return [
        'X-Forwarded-Host' => 'oithalo.example.local',
        'X-Forwarded-Port' => '8833',
        'X-Forwarded-Proto' => 'https',
    ];
}

test('generated urls use the public address forwarded by the proxy', function () {
    test()->actingAs(User::factory()->create())
        ->get('http://localhost:8880/sctenantgroups', forwardedHeaders())
        ->assertOk()
        ->assertSee('https://oithalo.example.local:8833/sctenantgroups/create', false)
        ->assertDontSee('localhost:8880', false);
});

test('the intended url after login uses the public address forwarded by the proxy', function () {
    test()->get('http://localhost:8880/sctenantgroups', forwardedHeaders())
        ->assertRedirect('https://oithalo.example.local:8833/login')
        ->assertSessionHas('url.intended', 'https://oithalo.example.local:8833/sctenantgroups');
});
