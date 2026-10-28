<?php

use App\Jobs\RefreshSCAlerts;
use App\Jobs\RefreshSCEndpoint;
use App\Jobs\RefreshSCEndpoints;
use App\Jobs\RefreshSCFirewalls;
use App\Models\SCEndpoint;
use App\Models\SCFirewall;
use App\Models\SCTenant;
use App\Models\User;
use App\Settings\SCServiceSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->actingAs(User::factory()->create());

    $settings = resolve(SCServiceSettings::class);
    $settings->token = 'test-token';
    $settings->token_expires_at = time() + 3600;
    $settings->alertsScheduleEnabled = true;
    $settings->endpointsScheduleEnabled = true;
    $settings->firewallsScheduleEnabled = false;
    $settings->save();
});

describe('tenant refresh menu', function () {
    test('queues the refresh of the chosen area for the tenant', function () {
        Queue::fake();
        $tenant = SCTenant::factory()->create();

        Volt::test('sctenant.refresh-menu', ['sctenant' => $tenant])
            ->call('refresh', 'alerts')
            ->assertSee('Alerts refresh queued.')
            ->call('refresh', 'endpoints');

        Queue::assertPushed(RefreshSCAlerts::class, 1);
        Queue::assertPushed(RefreshSCEndpoints::class, 1);
    });

    test('does not offer or queue areas whose feature is disabled', function () {
        Queue::fake();
        $tenant = SCTenant::factory()->create();

        Volt::test('sctenant.refresh-menu', ['sctenant' => $tenant])
            ->assertDontSee('Firewalls')
            ->call('refresh', 'firewalls');

        Queue::assertNotPushed(RefreshSCFirewalls::class);
    });

    test('does not queue anything for an ignored tenant', function () {
        Queue::fake();
        $tenant = SCTenant::factory()->create(['iscpp_ignore' => true]);

        Volt::test('sctenant.refresh-menu', ['sctenant' => $tenant])
            ->call('refresh', 'alerts')
            ->assertSee('ignored by ISCPP');

        Queue::assertNothingPushed();
    });
});

test('the endpoint refresh button queues a refresh of that endpoint', function () {
    Queue::fake();
    $endpoint = SCEndpoint::factory()->forTenant(SCTenant::factory()->create())->create();

    Volt::test('scendpoints.refresh-button', ['endpoint' => $endpoint])->call('refresh');

    Queue::assertPushed(RefreshSCEndpoint::class, fn (RefreshSCEndpoint $job) => $job->endpointId === $endpoint->id);
});

test('the firewall refresh button queues a refresh of its tenant firewalls', function () {
    Queue::fake();
    $tenant = SCTenant::factory()->create();
    $firewall = SCFirewall::factory()->forTenant($tenant)->create();

    Volt::test('scfirewalls.refresh-button', ['firewall' => $firewall])
        ->call('refresh')
        ->assertSee('all firewalls of this tenant');

    Queue::assertPushed(RefreshSCFirewalls::class, 1);
});

test('refreshing a single endpoint stores the api response', function () {
    $tenant = SCTenant::factory()->create(['apiHost' => 'https://api-eu02.central.sophos.com']);
    $endpoint = SCEndpoint::factory()->forTenant($tenant)->create(['hostname' => 'old-name']);

    Http::fake([
        'api-eu02.central.sophos.com/endpoint/v1/endpoints/'.$endpoint->id.'*' => Http::response([
            'id' => $endpoint->id,
            'type' => 'computer',
            'hostname' => 'new-name',
            'tamperProtectionEnabled' => true,
            'lastSeenAt' => '2026-10-28T08:00:00.000Z',
            'health' => ['overall' => 'suspicious'],
            'os' => ['platform' => 'windows', 'name' => 'Windows 11 Pro'],
        ]),
    ]);

    RefreshSCEndpoint::dispatchSync($endpoint->id);

    $endpoint->refresh();

    expect($endpoint->hostname)->toBe('new-name')
        ->and($endpoint->healthStatus)->toBe('suspicious')
        ->and($endpoint->rawData)->toBeArray()
        ->and(data_get($endpoint->rawData, 'os.name'))->toBe('Windows 11 Pro');
});

test('the tenant wide endpoint refresh stores raw data as an array', function () {
    $tenant = SCTenant::factory()->create(['apiHost' => 'https://api-eu02.central.sophos.com']);

    Http::fake([
        'api-eu02.central.sophos.com/endpoint/v1/endpoints*' => Http::response([
            'items' => [[
                'id' => '6a3b0d2e-1f3c-4c4f-9d1e-0a1b2c3d4e5f',
                'type' => 'server',
                'hostname' => 'srv-01',
                'tamperProtectionEnabled' => false,
                'lastSeenAt' => '2026-10-28T08:00:00.000Z',
                'health' => ['overall' => 'good'],
                'os' => ['platform' => 'linux', 'name' => 'Ubuntu'],
            ]],
            'pages' => ['total' => 1],
        ]),
    ]);

    RefreshSCEndpoints::dispatchSync($tenant);

    $endpoint = SCEndpoint::findOrFail('6a3b0d2e-1f3c-4c4f-9d1e-0a1b2c3d4e5f');

    expect($endpoint->rawData)->toBeArray()
        ->and(data_get($endpoint->rawData, 'os.name'))->toBe('Ubuntu');
});

test('the migration unwraps double encoded endpoint raw data', function () {
    $endpoint = SCEndpoint::factory()->forTenant(SCTenant::factory()->create())->create();
    DB::table('sc_endpoints')->where('id', $endpoint->id)->update([
        'rawData' => json_encode(json_encode(['hostname' => 'wrapped', 'os' => ['name' => 'Windows']])),
    ]);

    $migration = require database_path('migrations/2026_10_28_141628_decode_double_encoded_sc_endpoint_raw_data.php');
    $migration->up();

    expect(data_get($endpoint->fresh()->rawData, 'os.name'))->toBe('Windows');
});
