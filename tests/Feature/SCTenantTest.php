<?php

use App\Jobs\RefreshSCAlerts;
use App\Models\SCBillable;
use App\Models\SCEndpoint;
use App\Models\SCFirewall;
use App\Models\SCTenant;
use App\Models\User;
use App\Settings\HaloServiceSettings;
use Illuminate\Support\Facades\Queue;

test('guests are redirected to the login page for sctenants', function () {
    $response = $this->get('/sctenants');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the sctenants index page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/sctenants');
    $response->assertStatus(200);
});

test('authenticated users can view a specific sctenant', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $tenant = SCTenant::factory()->create();

    $response = $this->get(route('sctenants.tenantDetails', $tenant));
    $response->assertStatus(200);
});

test('sctenants show details view renders without missing components', function () {
    $tenant = SCTenant::factory()->make();

    $html = view('sctenants.show.details', [
        'sctenant' => $tenant,
    ])->render();

    expect($html)->toContain('Basic Information');
});

test('authenticated users can view tenant endpoints', function () {
    $user = User::factory()->create();
    $tenant = SCTenant::factory()->create();

    SCEndpoint::factory()
        ->forTenant($tenant)
        ->create([
            'hostname' => 'tenant-endpoint.example',
            'type' => 'computer',
            'healthStatus' => 'good',
            'tamperProtectionEnabled' => true,
        ]);

    $this->actingAs($user)
        ->get(route('sctenants.tenantEndpoints', $tenant))
        ->assertSuccessful()
        ->assertSee('tenant-endpoint.example');
});

test('authenticated users can view tenant firewalls', function () {
    $user = User::factory()->create();
    $tenant = SCTenant::factory()->create();

    SCFirewall::factory()
        ->forTenant($tenant)
        ->create([
            'hostname' => 'tenant-firewall.example',
            'rawData' => [
                'serialNumber' => 'X11103QPX83YHA2',
                'firmwareVersion' => 'XGS116_XN01_19.5.3.652',
                'status' => [
                    'connected' => true,
                    'suspended' => false,
                ],
                'updatedAt' => '2026-04-21T10:00:00.000Z',
            ],
        ]);

    $this->actingAs($user)
        ->get(route('sctenants.tenantFirewalls', $tenant))
        ->assertSuccessful()
        ->assertSee('tenant-firewall.example');
});

test('authenticated users can view tenant billables', function () {
    $user = User::factory()->create();
    $tenant = SCTenant::factory()->create();

    SCBillable::query()->create([
        'month' => now()->month,
        'year' => now()->year,
        'tenantId' => $tenant->id,
        'orderLineItemNumber' => '1001',
        'productGroup' => 'Endpoint',
        'billableQuantity' => 2,
        'orderedQuantity' => 2,
        'actualQuantity' => 2,
        'productCode' => 'endpointProtection',
        'sku' => 'SKU-1001',
        'productDescription' => 'Endpoint Protection',
        'rawData' => [],
        'sent_to_halo' => false,
    ]);

    $this->actingAs($user)
        ->get(route('sctenants.tenantBillables', $tenant))
        ->assertSuccessful()
        ->assertSee('Endpoint Protection');
});

test('ignored tenants are excluded from alert refresh dispatch command', function () {
    Queue::fake();

    $activeTenant = SCTenant::factory()->create([
        'iscpp_ignore' => false,
    ]);

    $ignoredTenant = SCTenant::factory()->create([
        'iscpp_ignore' => true,
    ]);

    $this->artisan('app:queue-refresh-scalerts-jobs-for-all-tenants')
        ->assertSuccessful();

    Queue::assertPushed(RefreshSCAlerts::class, function (RefreshSCAlerts $job) use ($activeTenant) {
        return (fn () => $this->tenantID)->call($job) === $activeTenant->id;
    });

    Queue::assertPushed(RefreshSCAlerts::class, 1);
    Queue::assertNotPushed(RefreshSCAlerts::class, function (RefreshSCAlerts $job) use ($ignoredTenant) {
        return (fn () => $this->tenantID)->call($job) === $ignoredTenant->id;
    });
});

test('ignored tenants are excluded from halo matching helper', function () {
    $user = User::factory()->create();

    $settings = app(HaloServiceSettings::class);
    $settings->enabled = true;
    $settings->save();

    $visibleTenant = SCTenant::factory()->create([
        'haloclient_id' => -1,
        'iscpp_ignore' => false,
        'name' => 'Visible Tenant',
    ]);

    $ignoredTenant = SCTenant::factory()->create([
        'haloclient_id' => -1,
        'iscpp_ignore' => true,
        'name' => 'Ignored Tenant',
    ]);

    $this->actingAs($user)
        ->get(route('sctenants.haloMatchingHelper'))
        ->assertSuccessful()
        ->assertSee($visibleTenant->name)
        ->assertDontSee($ignoredTenant->name);
});
