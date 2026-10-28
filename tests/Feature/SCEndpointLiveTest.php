<?php

use App\Models\Event;
use App\Models\SCEndpoint;
use App\Models\SCTenant;
use App\Models\User;
use App\Settings\SCServiceSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Livewire\Volt\Volt;

beforeEach(function () {
    Sleep::fake();
    $this->actingAs(User::factory()->create());

    $settings = resolve(SCServiceSettings::class);
    $settings->token = 'test-token';
    $settings->token_expires_at = time() + 3600;
    $settings->endpointsScheduleEnabled = true;
    $settings->save();

    $this->tenant = SCTenant::factory()->create(['apiHost' => 'https://api-eu02.central.sophos.com']);
    $this->endpoint = SCEndpoint::factory()->forTenant($this->tenant)->create(['type' => 'computer']);
});

/**
 * Fakes every endpoint live call. $overrides replaces single responses by path suffix.
 *
 * @param  array<string, mixed>  $overrides
 */
function fakeEndpointLiveApi(SCEndpoint $endpoint, array $overrides = []): void
{
    $base = 'api-eu02.central.sophos.com/endpoint/v1/';

    Http::fake(array_merge([
        $base.'endpoints/'.$endpoint->id.'/tamper-protection*' => Http::response([
            'enabled' => true,
            'password' => 'TOP-SECRET-PW',
            'previousPasswords' => [['password' => 'OLD-PW', 'invalidatedAt' => '2026-01-01T00:00:00Z']],
        ]),
        $base.'endpoints/'.$endpoint->id.'/isolation*' => Http::response([
            'enabled' => true,
            'lastEnabledAt' => '2026-10-27T10:00:00Z',
            'lastEnabledBy' => ['id' => 'admin@example.com', 'type' => 'user', 'name' => 'Admin'],
            'comment' => 'Suspicious traffic',
        ]),
        $base.'endpoints/'.$endpoint->id.'*' => Http::response([
            'id' => $endpoint->id,
            'type' => 'computer',
            'hostname' => $endpoint->hostname,
            'online' => true,
            'group' => ['id' => 'group-1', 'name' => 'Sales'],
            'health' => ['overall' => 'good', 'services' => ['serviceDetails' => [['name' => 'Sophos MCS Agent', 'status' => 'running']]]],
            'encryption' => ['overallStatus' => 'encrypted'],
        ]),
        $base.'policies*' => Http::response([
            'items' => [
                ['name' => 'Direct Policy', 'type' => 'threat-protection', 'enabled' => true, 'priority' => 1, 'appliesTo' => ['endpoints' => [$endpoint->id]]],
                ['name' => 'Group Policy', 'type' => 'web-control', 'enabled' => true, 'priority' => 2, 'appliesTo' => ['endpointGroups' => ['group-1']]],
                ['name' => 'Base Policy', 'type' => 'agent-updating', 'enabled' => true, 'priority' => 0],
                ['name' => 'Other Endpoint Policy', 'type' => 'threat-protection', 'enabled' => true, 'priority' => 3, 'appliesTo' => ['endpoints' => ['someone-else']]],
                ['name' => 'Server Base Policy', 'type' => 'server-threat-protection', 'enabled' => true, 'priority' => 0],
            ],
            'pages' => ['total' => 1],
        ]),
    ], $overrides));
}

test('the live tab is reachable', function () {
    $this->get(route('scendpoints.endpointLive', ['id' => $this->endpoint->id]))
        ->assertOk()
        ->assertSee('Loading live data');
});

test('the live tab shows status, isolation and tamper protection without the password', function () {
    fakeEndpointLiveApi($this->endpoint);

    Volt::test('scendpoints.live', ['endpoint' => $this->endpoint])
        ->call('load')
        ->assertSee('Sophos MCS Agent')
        ->assertSee('Isolated')
        ->assertSee('Suspicious traffic')
        ->assertSee('2026-01-01T00:00:00Z')
        ->assertDontSee('TOP-SECRET-PW')
        ->assertDontSee('OLD-PW');
});

test('revealing the tamper protection password shows it and records who did it', function () {
    fakeEndpointLiveApi($this->endpoint);

    Volt::test('scendpoints.live', ['endpoint' => $this->endpoint])
        ->call('load')
        ->call('revealPassword')
        ->assertSee('TOP-SECRET-PW')
        ->call('hidePassword')
        ->assertDontSee('TOP-SECRET-PW');

    $event = Event::where('event', 'scendpoints')->get()->first(fn (Event $event) => ($event->data['message'] ?? '') === 'Tamper protection password revealed');

    expect($event)->not->toBeNull()
        ->and($event->data['endpoint'])->toBe($this->endpoint->id)
        ->and($event->data)->not->toHaveKey('password');
});

test('the live tab lists only policies attributable to the endpoint', function () {
    fakeEndpointLiveApi($this->endpoint);

    Volt::test('scendpoints.live', ['endpoint' => $this->endpoint])
        ->call('load')
        ->assertSee('Direct Policy')
        ->assertSee('Group Policy')
        ->assertSee('Base Policy')
        ->assertDontSee('Other Endpoint Policy')
        ->assertDontSee('Server Base Policy');
});

test('a failing section is reported without hiding the others', function () {
    fakeEndpointLiveApi($this->endpoint, [
        'api-eu02.central.sophos.com/endpoint/v1/endpoints/'.$this->endpoint->id.'/isolation*' => Http::response(['error' => 'forbidden'], 403),
    ]);

    Volt::test('scendpoints.live', ['endpoint' => $this->endpoint])
        ->call('load')
        ->assertSee('Could not load the isolation state.')
        ->assertSee('Sophos MCS Agent');
});

test('the tenant endpoint settings tab lists policies, blocked items and blocked addresses', function () {
    $base = 'api-eu02.central.sophos.com/endpoint/v1/';
    Http::fake([
        $base.'policies*' => Http::response(['items' => [['name' => 'Base Policy', 'type' => 'threat-protection', 'enabled' => true, 'priority' => 0]], 'pages' => ['total' => 1]]),
        $base.'settings/blocked-items*' => Http::response(['items' => [['comment' => 'Known bad', 'properties' => ['fileName' => 'evil.exe', 'sha256' => str_repeat('a', 64)]]], 'pages' => ['total' => 1]]),
        $base.'settings/blocked-addresses*' => Http::response(['items' => [['item' => '203.0.113.7', 'comment' => 'Scanner']], 'pages' => ['total' => 1]]),
    ]);

    Volt::test('sctenant.endpoint-settings', ['sctenant' => $this->tenant])
        ->call('load')
        ->assertSee('Base Policy')
        ->assertSee('evil.exe')
        ->assertSee('203.0.113.7');
});

test('the tenant endpoint settings tab is hidden while endpoints are disabled', function () {
    $settings = resolve(SCServiceSettings::class);
    $settings->endpointsScheduleEnabled = false;
    $settings->save();

    $this->get(route('sctenants.tenantDetails', $this->tenant))
        ->assertOk()
        ->assertDontSee('Endpoint Settings');
});

test('the endpoint details page reads the stored raw data', function () {
    $this->get(route('scendpoints.endpointDetails', ['id' => $this->endpoint->id]))
        ->assertOk()
        ->assertSee(trim(data_get($this->endpoint->rawData, 'os.name')))
        ->assertSee(data_get($this->endpoint->rawData, 'ipv4Addresses.0'));
});

test('the endpoint list can be filtered by health', function () {
    SCEndpoint::factory()->forTenant($this->tenant)->create(['hostname' => 'bad-host', 'healthStatus' => 'bad']);

    $this->get(route('scendpoints.index', ['filterHealth' => 'bad']))
        ->assertOk()
        ->assertSee('bad-host')
        ->assertDontSee($this->endpoint->hostname);
});
