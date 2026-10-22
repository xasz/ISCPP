<?php

use App\Events\ISCPPEvent;
use App\Events\ISCPPEventBase;
use App\Events\ISCPPEventSeverity;
use App\Events\ISCPPSCAlertCreated;
use App\Events\ISCPPSCTenantCreated;
use App\Events\ISCPPSCTenantRemoved;
use App\Models\ISCPPEventLog;
use App\Models\SCAlert;
use App\Models\SCTenant;
use App\Services\ISCPPEventRegistry;
use App\Services\ISCPPEvents;

beforeEach(function () {
    config()->set('iscpp.events.enabled', true);
});

/**
 * The single most important test in this suite.
 *
 * Laravel resolves listeners through class_implements(), so a listener registered against
 * a parent class never fires for its subclasses. The whole pipeline depends on the marker
 * being an interface; if that ever regresses, nothing is recorded and nothing is delivered.
 */
test('the global listener catches a concrete event subclass', function () {
    $tenant = SCTenant::factory()->create();

    ISCPPSCTenantCreated::dispatch($tenant);

    $event = ISCPPEventLog::where('key', 'sc.tenant.created')->latest('id')->first();

    expect($event)->not->toBeNull()
        ->and($event->tenantId)->toBe($tenant->id)
        ->and($event->severity)->toBe(ISCPPEventSeverity::Info);
});

test('the marker is an interface so it propagates through the abstract base', function () {
    expect(class_implements(ISCPPSCTenantCreated::class))->toHaveKey(ISCPPEvent::class);
});

/**
 * Guards against the recorder being registered twice.
 *
 * Laravel auto-discovers listeners in app/Listeners by their handle() signature, so
 * naming the recorder's method handle() would register it on top of the explicit
 * Event::listen() in AppServiceProvider and record every event twice.
 */
test('one dispatch records exactly one row', function () {
    $tenant = SCTenant::factory()->create();
    ISCPPEventLog::query()->delete();

    ISCPPSCTenantCreated::dispatch($tenant);

    expect(ISCPPEventLog::count())->toBe(1);
});

/**
 * The base class deliberately has no constructor contract: a subclass using promoted
 * properties must not have to call parent::__construct().
 */
test('an event using only a promoted constructor produces a stable uuid and timestamp', function () {
    $tenant = SCTenant::factory()->create();
    $event = new ISCPPSCTenantCreated($tenant);

    expect($event->uuid())->toBe($event->uuid())
        ->and($event->uuid())->toBeString()
        ->and($event->occurredAt()->toIso8601String())->toBe($event->occurredAt()->toIso8601String());
});

test('the envelope has the documented shape', function () {
    $tenant = SCTenant::factory()->create(['haloclient_id' => 42]);

    ISCPPSCTenantCreated::dispatch($tenant);

    $payload = ISCPPEventLog::latest('id')->first()->payload;

    expect($payload)->toHaveKeys(['iscpp', 'event', 'tenant', 'subject', 'data'])
        ->and($payload['iscpp']['payloadVersion'])->toBe(1)
        ->and($payload['event']['key'])->toBe('sc.tenant.created')
        ->and($payload['event']['severity'])->toBe('info')
        ->and($payload['tenant']['id'])->toBe($tenant->id)
        ->and($payload['tenant']['haloclientId'])->toBe(42)
        ->and($payload['subject']['type'])->toBe('SCTenant');
});

test('the registry discovers every event class and excludes the base and enum', function () {
    $registry = app(ISCPPEventRegistry::class);
    $keys = $registry->keys();

    expect($keys)->toContain('sc.tenant.created', 'sc.tenant.removed', 'sc.endpoint.created',
        'sc.endpoint.removed', 'sc.alert.created', 'sc.alert.acknowledged', 'sc.alert.removed',
        'sc.firewall.created', 'sc.firewall.removed', 'healthscore.created', 'healthscore.removed',
        'healthscore.threshold.dropped', 'healthscore.threshold.recovered');

    foreach ($registry->all() as $class) {
        expect(is_subclass_of($class, ISCPPEventBase::class))->toBeTrue()
            ->and((new ReflectionClass($class))->isAbstract())->toBeFalse();
    }
});

test('the tree nests events by their key segments', function () {
    $tree = collect(app(ISCPPEventRegistry::class)->tree())->keyBy('segment');

    $healthscore = collect($tree['healthscore']['children'])->keyBy('segment');
    $threshold = collect($healthscore['threshold']['children'])->keyBy('segment');

    expect($tree['sc']['label'])->toBe('Sophos Central')
        ->and($healthscore['created']['key'])->toBe('healthscore.created')
        ->and($threshold['dropped']['key'])->toBe('healthscore.threshold.dropped')
        ->and($threshold['dropped']['path'])->toBe('healthscore.threshold.dropped')
        ->and($threshold['dropped']['severity'])->toBe(ISCPPEventSeverity::Warning)
        ->and($healthscore['threshold']['key'])->toBeNull();

    $leafKeys = [];
    $walk = function (array $nodes) use (&$walk, &$leafKeys) {
        foreach ($nodes as $node) {
            if ($node['key'] !== null) {
                $leafKeys[] = $node['key'];
            }
            $walk($node['children']);
        }
    };
    $walk($tree->values()->all());

    // Every registered event appears exactly once in the tree.
    expect(collect($leafKeys)->sort()->values()->all())
        ->toBe(collect(app(ISCPPEventRegistry::class)->keys())->sort()->values()->all());
});

test('branches are listed before leaves', function () {
    $healthscore = collect(app(ISCPPEventRegistry::class)->tree())->firstWhere('segment', 'healthscore');

    expect($healthscore['children'][0]['segment'])->toBe('threshold');
});

test('the catalog describes an event from its class', function () {
    $alert = app(ISCPPEventRegistry::class)->describe('sc.alert.created');

    expect($alert['severity'])->toBe(ISCPPEventSeverity::Warning)
        ->and($alert['severityVaries'])->toBeFalse()
        ->and($alert['description'])->not->toBe('')
        ->and(collect($alert['properties'])->pluck('name'))->toContain('severity', 'category')
        ->and(collect($alert['properties'])->firstWhere('name', 'severity')['type'])->toBe('string');
});

/**
 * An event whose severity depends on the individual occurrence.
 */
class VaryingSeverityISCPPEvent extends ISCPPEventBase
{
    public function __construct(public ISCPPEventSeverity $level) {}

    public static function key(): string
    {
        return 'test.varying';
    }

    public static function label(): string
    {
        return 'Varying';
    }

    public function data(): array
    {
        return [];
    }

    public function severity(): ISCPPEventSeverity
    {
        return $this->level;
    }
}

test('an event overriding severity() is marked as varying per occurrence', function () {
    expect(ISCPPEventRegistry::severityVaries(VaryingSeverityISCPPEvent::class))->toBeTrue()
        ->and(ISCPPEventRegistry::severityVaries(ISCPPSCTenantCreated::class))->toBeFalse();
});

test('an occurrence uses the default severity unless the event overrides it', function () {
    $tenant = SCTenant::factory()->create();

    expect((new ISCPPSCTenantRemoved($tenant))->severity())->toBe(ISCPPEventSeverity::Warning)
        ->and((new VaryingSeverityISCPPEvent(ISCPPEventSeverity::Error))->severity())->toBe(ISCPPEventSeverity::Error);
});

test('events are not recorded when the system is disabled', function () {
    config()->set('iscpp.events.enabled', false);
    $tenant = SCTenant::factory()->create();

    ISCPPSCTenantCreated::dispatch($tenant);

    expect(ISCPPEventLog::count())->toBe(0);
});

test('creating a tenant raises the created event through its observer', function () {
    SCTenant::factory()->create();

    expect(ISCPPEventLog::where('key', 'sc.tenant.created')->count())->toBe(1);
});

test('muting suppresses recording and restores itself after an exception', function () {
    // Creating the tenant already raises SCTenantCreated through its observer, so the
    // log is cleared first and everything below is measured from zero.
    $tenant = SCTenant::factory()->create();
    ISCPPEventLog::query()->delete();

    ISCPPEvents::mute(fn () => ISCPPSCTenantCreated::dispatch($tenant));
    expect(ISCPPEventLog::count())->toBe(0);

    try {
        ISCPPEvents::mute(function () {
            throw new RuntimeException('boom');
        });
    } catch (RuntimeException) {
        // expected
    }

    expect(ISCPPEvents::muted())->toBeFalse();

    ISCPPSCTenantCreated::dispatch($tenant);
    expect(ISCPPEventLog::count())->toBe(1);
});

test('muting nests without un-muting early', function () {
    $tenant = SCTenant::factory()->create();
    ISCPPEventLog::query()->delete();

    ISCPPEvents::mute(function () use ($tenant) {
        ISCPPEvents::mute(fn () => null);

        // Still muted: the inner call must not clear the outer one.
        ISCPPSCTenantCreated::dispatch($tenant);
    });

    expect(ISCPPEventLog::count())->toBe(0);
});

test('events for ignored tenants are not recorded', function () {
    $tenant = SCTenant::factory()->create(['iscpp_ignore' => true]);

    ISCPPSCTenantCreated::dispatch($tenant);

    expect(ISCPPEventLog::count())->toBe(0);
});

test('an alert event carries the alert severity for rule conditions', function () {
    $tenant = SCTenant::factory()->create();
    $alert = SCAlert::factory()->create(['tenantId' => $tenant->id, 'severity' => 'high']);

    ISCPPSCAlertCreated::dispatch($alert);

    $payload = ISCPPEventLog::where('key', 'sc.alert.created')->latest('id')->first()->payload;

    expect($payload['data']['severity'])->toBe('high')
        ->and($payload['event']['severity'])->toBe('warning');
});
