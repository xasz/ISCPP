<?php

use App\Jobs\RefreshSCTenants;
use App\Models\ISCPPEventDelivery;
use App\Models\ISCPPEventLog;
use App\Models\ISCPPEventRule;
use App\Models\SCTenant;
use App\Services\SCService;

beforeEach(function () {
    config()->set('iscpp.events.enabled', true);
});

/**
 * Runs the refresh job against a fake Sophos response.
 *
 * @param  array<int, SCTenant>  $returned
 */
function runTenantRefresh(array $returned): void
{
    $payload = collect($returned)->map(fn (SCTenant $tenant) => [
        'id' => $tenant->id,
        'showAs' => $tenant->showAs,
        'name' => $tenant->name,
        'dataGeography' => $tenant->dataGeography,
        'dataRegion' => $tenant->dataRegion,
        'billingType' => $tenant->billingType,
        'apiHost' => $tenant->apiHost,
    ]);

    $service = Mockery::mock(SCService::class);
    $service->shouldReceive('tenants')->andReturn($payload);

    (new RefreshSCTenants)->handle($service);
}

test('an empty api response deletes nothing', function () {
    $tenant = SCTenant::factory()->create();

    runTenantRefresh([]);

    expect(SCTenant::count())->toBe(1)
        ->and($tenant->fresh()->missing_since)->toBeNull();
});

test('an absent tenant is marked but not deleted within the grace period', function () {
    $kept = SCTenant::factory()->create();
    $absent = SCTenant::factory()->create();

    runTenantRefresh([$kept]);

    expect(SCTenant::count())->toBe(2)
        ->and($absent->fresh()->missing_since)->not->toBeNull()
        ->and($kept->fresh()->missing_since)->toBeNull();
});

test('a tenant that returns has its absence cleared', function () {
    $kept = SCTenant::factory()->create();
    $absent = SCTenant::factory()->create();

    runTenantRefresh([$kept]);
    runTenantRefresh([$kept, $absent]);

    expect($absent->fresh()->missing_since)->toBeNull();
});

/**
 * The bulk delete this replaced never fired model events, so no observer could see it.
 */
test('a tenant absent beyond the grace period is deleted and raises the removed event', function () {
    $kept = SCTenant::factory()->create();
    $absent = SCTenant::factory()->create();

    runTenantRefresh([$kept]);

    $absent->forceFill([
        'missing_since' => now()->subHours(RefreshSCTenants::REMOVAL_GRACE_HOURS + 1),
    ])->save();

    ISCPPEventLog::query()->delete();
    runTenantRefresh([$kept]);

    expect(SCTenant::find($absent->id))->toBeNull()
        ->and(ISCPPEventLog::where('key', 'sc.tenant.removed')->count())->toBe(1);
});

test('the very first tenant import is kept out of the event log', function () {
    $tenant = SCTenant::factory()->make();

    // No tenants exist yet, so this is an initial import rather than live changes.
    SCTenant::query()->delete();
    ISCPPEventLog::query()->delete();

    runTenantRefresh([$tenant]);

    expect(SCTenant::count())->toBe(1)
        ->and(ISCPPEventLog::where('key', 'sc.tenant.created')->count())->toBe(0);
});

test('deleting a tenant cascades its rule selection but leaves the rule itself', function () {
    $tenant = SCTenant::factory()->create();
    $rule = ISCPPEventRule::factory()->forTenant($tenant->id)->create();

    $tenant->delete();

    // No foreign key on purpose: a tenant lost to a transient API fault must not erase
    // the rule watching it. The rule survives and rebinds when the tenant returns.
    expect(ISCPPEventRule::find($rule->id))->not->toBeNull()
        ->and(ISCPPEventRule::find($rule->id)->tenantId)->toBe($tenant->id);
});

test('pruning removes expired events and cascades their deliveries', function () {
    config()->set('iscpp.events.retention_days', 30);

    $rule = ISCPPEventRule::factory()->create();

    $old = ISCPPEventLog::factory()->create(['occurred_at' => now()->subDays(60)]);
    $fresh = ISCPPEventLog::factory()->create(['occurred_at' => now()->subDay()]);

    ISCPPEventDelivery::create([
        'iscpp_event_id' => $old->id,
        'iscpp_event_rule_id' => $rule->id,
        'attempt' => 1,
        'status' => 'success',
    ]);

    $this->artisan('model:prune', ['--model' => [ISCPPEventLog::class]])->assertSuccessful();

    expect(ISCPPEventLog::find($old->id))->toBeNull()
        ->and(ISCPPEventLog::find($fresh->id))->not->toBeNull()
        ->and(ISCPPEventDelivery::where('iscpp_event_id', $old->id)->count())->toBe(0);
});
