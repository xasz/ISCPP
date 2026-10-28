<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Livewire\Volt\Volt;

test('guests are redirected to the login page for general settings', function () {
    $response = $this->get('/generalsettings');
    $response->assertRedirect('/login');
});

test('authenticated users are redirected from general settings root to sophos central settings page', function () {
    $user = User::factory()->superAdmin()->create();
    $this->actingAs($user);

    $response = $this->get('/generalsettings');
    $response->assertRedirect(route('generalsettings.sc'));
});

test('authenticated users can visit each general settings page', function (string $routeName) {
    $user = User::factory()->superAdmin()->create();
    $this->actingAs($user);

    $response = $this->get(route($routeName));
    $response->assertOk();
})->with([
    'generalsettings.sc',
    'generalsettings.webhookAlerts',
    'generalsettings.halo',
    'generalsettings.ninja',
    'generalsettings.commands',
]);

test('guests are redirected to the login page for each general settings page', function (string $path) {
    $response = $this->get($path);
    $response->assertRedirect('/login');
})->with([
    '/generalsettings/sc',
    '/generalsettings/webhook-alerts',
    '/generalsettings/halo',
    '/generalsettings/ninja',
    '/generalsettings/commands',
]);

test('the all alerts command button queues a full alert refresh', function () {
    $this->actingAs(User::factory()->superAdmin()->create());

    $component = Volt::test('settings.card-commands');

    Artisan::shouldReceive('call')
        ->once()
        ->with('app:queue-refresh-all-scalerts-jobs-for-all-tenants');

    $component->call('allAlerts');
});
