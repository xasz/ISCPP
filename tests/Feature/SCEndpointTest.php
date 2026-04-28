<?php

use App\Models\SCEndpoint;
use App\Models\SCTenant;
use App\Models\User;
use App\Settings\SCServiceSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page for scendpoints', function () {
    $response = $this->get('/scendpoints');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the scendpoints index page', function () {
    $settings = resolve(SCServiceSettings::class);
    $settings->endpointsScheduleEnabled = true;
    $settings->save();

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/scendpoints');
    $response->assertSuccessful();
});

test('authenticated users can view specific endpoint details page', function () {
    $settings = resolve(SCServiceSettings::class);
    $settings->endpointsScheduleEnabled = true;
    $settings->save();

    $user = User::factory()->create();
    $tenant = SCTenant::factory()->create();
    $endpoint = SCEndpoint::factory()->forTenant($tenant)->create();

    $this->actingAs($user);

    $this->get(route('scendpoints.endpointDetails', ['id' => $endpoint->id]))
        ->assertSuccessful()
        ->assertSee('Endpoint Details')
        ->assertSee($endpoint->hostname)
        ->assertDontSee('Json Data');
});

test('authenticated users can view specific endpoint raw page', function () {
    $settings = resolve(SCServiceSettings::class);
    $settings->endpointsScheduleEnabled = true;
    $settings->save();

    $user = User::factory()->create();
    $tenant = SCTenant::factory()->create();
    $endpoint = SCEndpoint::factory()->forTenant($tenant)->create();

    $this->actingAs($user);

    $this->get(route('scendpoints.endpointRaw', ['id' => $endpoint->id]))
        ->assertSuccessful()
        ->assertSee('Json Data')
        ->assertSee($endpoint->hostname);
});
