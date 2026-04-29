<?php

use App\Models\SCFirewall;
use App\Models\SCTenant;
use App\Settings\SCServiceSettings;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Volt\Volt as LivewireVolt;

test('firmware card loads available updates from sophos response', function () {
    $tenant = SCTenant::factory()->create([
        'apiHost' => 'https://api-eu02.central.sophos.com',
    ]);

    $firewall = SCFirewall::factory()->forTenant($tenant)->create([
        'id' => 'c537aec2-56bb-4dba-b0ff-b76822396c80',
        'rawData' => [
            'id' => 'c537aec2-56bb-4dba-b0ff-b76822396c80',
            'tenant' => ['id' => $tenant->id],
            'serialNumber' => 'X31017X84X6DG72',
            'hostname' => 'fw-edge-01',
            'firmwareVersion' => '21.5.1.261',
        ],
    ]);

    $settings = resolve(SCServiceSettings::class);
    $settings->token = 'test-token';
    $settings->token_expires_at = time() + 3600;
    $settings->save();

    Http::fake([
        'https://api-eu02.central.sophos.com/firewall/v1/firewalls/actions/firmware-upgrade-check' => Http::response([
            'firewalls' => [
                [
                    'id' => 'c537aec2-56bb-4dba-b0ff-b76822396c80',
                    'serialNumber' => 'X31017X84X6DG72',
                    'firmwareVersion' => '21.5.1.261',
                    'upgradeToVersion' => '22.0.0.411',
                ],
            ],
            'firmwareVersions' => [
                [
                    'version' => '22.0.0.411',
                    'size' => '907 MB',
                    'news' => 'Feature Release',
                    'bugs' => 'NC-101839',
                ],
            ],
        ], 201),
    ]);

    LivewireVolt::test('scfirewalls.card-firmware-upgrade', ['firewall' => $firewall])
        ->call('check')
        ->assertSet('selectedVersion', '22.0.0.411')
        ->assertSee('22.0.0.411')
        ->assertSee('907 MB')
        ->assertSee('Plan Upgrade');
});

test('firmware card sends upgrade planning payload expected by sophos api', function () {
    $tenant = SCTenant::factory()->create([
        'apiHost' => 'https://api-eu02.central.sophos.com',
    ]);

    $firewall = SCFirewall::factory()->forTenant($tenant)->create([
        'id' => 'c537aec2-56bb-4dba-b0ff-b76822396c80',
        'rawData' => [
            'id' => 'c537aec2-56bb-4dba-b0ff-b76822396c80',
            'tenant' => ['id' => $tenant->id],
            'serialNumber' => 'X31017X84X6DG72',
            'hostname' => 'fw-edge-01',
            'firmwareVersion' => '21.5.1.261',
        ],
    ]);

    $settings = resolve(SCServiceSettings::class);
    $settings->token = 'test-token';
    $settings->token_expires_at = time() + 3600;
    $settings->save();

    $capturedBody = null;

    Http::fake(function (Request $request) use (&$capturedBody) {
        if ($request->url() === 'https://api-eu02.central.sophos.com/firewall/v1/firewalls/actions/firmware-upgrade') {
            $capturedBody = $request->data();

            return Http::response([
                'firewalls' => [
                    [
                        'id' => 'c537aec2-56bb-4dba-b0ff-b76822396c80',
                        'upgradeToVersion' => '22.0.0.411',
                        'upgradeAt' => '2026-04-28T14:35:00.000Z',
                        'status' => 'scheduled',
                    ],
                ],
            ], 201);
        }

        return Http::response([], 404);
    });

    LivewireVolt::test('scfirewalls.card-firmware-upgrade', ['firewall' => $firewall])
        ->set('selectedVersion', '22.0.0.411')
        ->set('upgradeAt', '2026-04-28T14:35')
        ->call('plan')
        ->assertSee('Firmware upgrade scheduled successfully.')
        ->assertSee('scheduled');

    expect($capturedBody)->toBe([
        'firewalls' => [
            [
                'id' => 'c537aec2-56bb-4dba-b0ff-b76822396c80',
                'upgradeAt' => '2026-04-28T14:35:00.000Z',
                'upgradeToVersion' => '22.0.0.411',
            ],
        ],
    ]);
});

test('firmware card sends cancel request with firewall id as ids query', function () {
    $tenant = SCTenant::factory()->create([
        'apiHost' => 'https://api-eu02.central.sophos.com',
    ]);

    $firewall = SCFirewall::factory()->forTenant($tenant)->create([
        'id' => 'c537aec2-56bb-4dba-b0ff-b76822396c80',
    ]);

    $settings = resolve(SCServiceSettings::class);
    $settings->token = 'test-token';
    $settings->token_expires_at = time() + 3600;
    $settings->save();

    $capturedIds = null;
    $capturedMethod = null;

    Http::fake(function (Request $request) use (&$capturedIds, &$capturedMethod) {
        if (str_starts_with($request->url(), 'https://api-eu02.central.sophos.com/firewall/v1/firewalls/actions/firmware-upgrade')) {
            $capturedMethod = $request->method();

            $capturedIds = data_get($request->data(), 'ids');

            $query = [];
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);
            if ($capturedIds === null) {
                $capturedIds = $query['ids'] ?? null;
            }

            return Http::response([
                'deleted' => true,
            ], 200);
        }

        return Http::response([], 404);
    });

    LivewireVolt::test('scfirewalls.card-firmware-upgrade', ['firewall' => $firewall])
        ->set('checked', true)
        ->call('cancel')
        ->assertSee('Firmware upgrade cancelled successfully.');

    expect($capturedMethod)->toBe('DELETE');
    expect($capturedIds)->toBe('c537aec2-56bb-4dba-b0ff-b76822396c80');
});

test('firmware card shows friendly error when sophos credentials are missing', function () {
    $tenant = SCTenant::factory()->create([
        'apiHost' => 'https://api-eu02.central.sophos.com',
    ]);

    $firewall = SCFirewall::factory()->forTenant($tenant)->create();

    $settings = resolve(SCServiceSettings::class);
    $settings->clientId = '';
    $settings->clientSecret = '';
    $settings->token = null;
    $settings->token_expires_at = null;
    $settings->save();

    LivewireVolt::test('scfirewalls.card-firmware-upgrade', ['firewall' => $firewall])
        ->call('check')
        ->assertSee('Sophos Central Partner credentials not found');
});
