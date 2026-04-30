<?php

use App\Models\SCFirewall;
use App\Models\SCTenant;
use App\Models\User;
use App\Settings\SCServiceSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page for scfirewalls', function () {
    $response = $this->get('/scfirewalls');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the scfirewalls index page', function () {

    $settings = resolve(SCServiceSettings::class);
    $settings->firewallsScheduleEnabled = true;
    $settings->save();

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/scfirewalls');
    $response->assertSuccessful();
});

test('authenticated users can view specific firewall details page', function () {
    $settings = resolve(SCServiceSettings::class);
    $settings->firewallsScheduleEnabled = true;
    $settings->save();

    $user = User::factory()->create();
    $tenant = SCTenant::factory()->create();
    $firewall = SCFirewall::factory()->forTenant($tenant)->create();

    $this->actingAs($user);

    $this->get(route('scfirewalls.firewallDetails', ['id' => $firewall->id]))
        ->assertSuccessful()
        ->assertSee('Firewall Details')
        ->assertSee($firewall->hostname)
        ->assertDontSee('Json Data');
});

test('authenticated users can view specific firewall raw page', function () {
    $settings = resolve(SCServiceSettings::class);
    $settings->firewallsScheduleEnabled = true;
    $settings->save();

    $user = User::factory()->create();
    $tenant = SCTenant::factory()->create();
    $firewall = SCFirewall::factory()->forTenant($tenant)->create();

    $this->actingAs($user);

    $this->get(route('scfirewalls.firewallRaw', ['id' => $firewall->id]))
        ->assertSuccessful()
        ->assertSee('Json Data')
        ->assertSee($firewall->hostname);
});

test('authenticated users can view specific firewall firmware page', function () {
    $settings = resolve(SCServiceSettings::class);
    $settings->firewallsScheduleEnabled = true;
    $settings->save();

    $user = User::factory()->create();
    $tenant = SCTenant::factory()->create();
    $firewall = SCFirewall::factory()->forTenant($tenant)->create();

    $this->actingAs($user);

    $this->get(route('scfirewalls.firewallFirmware', ['id' => $firewall->id]))
        ->assertSuccessful()
        ->assertSee('Firmware Upgrade')
        ->assertSee('Check Firmware Updates')
        ->assertSee($firewall->hostname);
});
