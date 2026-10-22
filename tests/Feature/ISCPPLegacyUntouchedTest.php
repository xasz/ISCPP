<?php

use App\Events\ISCPPEvent;
use App\Events\ISCPPEventBase;
use App\Events\ISCPPEventSeverity;
use App\Models\Event;
use App\Models\ISCPPEventLog;
use App\Models\SCAlert;
use App\Models\SCTenant;
use App\Models\User;
use App\Models\WebhookLog;
use App\Settings\WebhookSettings;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('iscpp.events.enabled', true);
});

/**
 * An event whose data() throws, used to prove the recorder cannot break anything.
 */
class ExplodingISCPPEvent extends ISCPPEventBase
{
    public function __construct(public SCAlert $scalert) {}

    public static function key(): string
    {
        return 'test.exploding';
    }

    public static function label(): string
    {
        return 'Exploding';
    }

    public function data(): array
    {
        throw new RuntimeException('data() blew up');
    }
}

test('the exploding test event really does implement the marker', function () {
    expect(class_implements(ExplodingISCPPEvent::class))->toHaveKey(ISCPPEvent::class);
});

/**
 * The ISCPP observer runs first in the SCAlert chain, so a failure inside it would
 * otherwise take auto-acknowledge and the legacy webhook down with it.
 */
test('an event that throws is swallowed and logged rather than propagated', function () {
    $tenant = SCTenant::factory()->create();
    $alert = SCAlert::factory()->create(['tenantId' => $tenant->id]);

    // The absence of a thrown exception here is the assertion: dispatch() must return
    // normally even though the event's data() throws.
    ExplodingISCPPEvent::dispatch($alert);

    expect(Event::where('event', 'iscpp')->where('type', 'error')->count())->toBeGreaterThan(0);
});

test('the legacy SCAlert webhook still fires when ISCPP events are enabled', function () {
    Http::fake(['legacy.example.com/*' => Http::response('ok', 200)]);

    $settings = app(WebhookSettings::class);
    $settings->enabled = true;
    $settings->url = 'https://legacy.example.com/hook';
    $settings->save();

    $tenant = SCTenant::factory()->create();
    SCAlert::factory()->create(['tenantId' => $tenant->id]);

    // The legacy path writes its own log table, entirely separate from iscpp_events.
    expect(WebhookLog::count())->toBeGreaterThan(0);
    Http::assertSent(fn ($request) => $request->url() === 'https://legacy.example.com/hook');
});

test('the legacy system log is untouched by the new system', function () {
    $before = Event::count();

    $tenant = SCTenant::factory()->create();

    // The new system writes only to iscpp_events; it must not add rows to the legacy log.
    expect(ISCPPEventLog::count())->toBeGreaterThan(0)
        ->and(Event::count())->toBe($before);
});

test('the legacy events page still works and is separate from the ISCPP log', function () {
    $this->actingAs(User::factory()->create());

    Event::logInfo('legacy', 'still here');

    $this->get('/events')->assertStatus(200)->assertSee('still here');
});

test('severity levels cover the legacy log levels for a later migration', function () {
    expect(ISCPPEventSeverity::tryFrom('info'))->not->toBeNull()
        ->and(ISCPPEventSeverity::tryFrom('warning'))->not->toBeNull()
        ->and(ISCPPEventSeverity::tryFrom('error'))->not->toBeNull()
        ->and(ISCPPEventSeverity::tryFrom('critical'))->not->toBeNull();
});
