<?php

use App\Events\ISCPPSCTenantCreated;
use App\Jobs\DeliverISCPPWebhook;
use App\Models\ISCPPEventDelivery;
use App\Models\ISCPPEventLog;
use App\Models\ISCPPEventRule;
use App\Models\SCTenant;
use App\Services\ISCPPWebhookSender;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('iscpp.events.enabled', true);
});

test('a matching rule delivers the envelope to its webhook', function () {
    Http::fake(['hooks.example.com/*' => Http::response('ok', 200)]);

    ISCPPEventRule::factory()->create(['url' => 'https://hooks.example.com/target']);
    ISCPPEventRule::flushCache();

    $tenant = SCTenant::factory()->create();
    ISCPPSCTenantCreated::dispatch($tenant);

    $event = ISCPPEventLog::where('key', 'sc.tenant.created')->latest('id')->first();

    Http::assertSent(function ($request) use ($event) {
        $body = json_decode($request->body(), true);

        return $request->url() === 'https://hooks.example.com/target'
            && $request->hasHeader('Idempotency-Key', $event->uuid)
            && $body['event']['key'] === 'sc.tenant.created';
    });

    expect(ISCPPEventDelivery::where('status', 'success')->count())->toBeGreaterThan(0);
});

test('nothing is delivered when no rule matches, but the event is still recorded', function () {
    Http::fake();

    $tenant = SCTenant::factory()->create();
    ISCPPSCTenantCreated::dispatch($tenant);

    Http::assertNothingSent();
    expect(ISCPPEventLog::where('key', 'sc.tenant.created')->count())->toBeGreaterThan(0)
        ->and(ISCPPEventDelivery::count())->toBe(0);
});

test('basic auth credentials on the rule are sent', function () {
    Http::fake(['hooks.example.com/*' => Http::response('ok', 200)]);

    $rule = ISCPPEventRule::factory()->create([
        'url' => 'https://hooks.example.com/auth',
        'basic_auth_username' => 'iscpp',
        'basic_auth_password' => 'secret',
    ]);
    $event = ISCPPEventLog::factory()->create();

    (new DeliverISCPPWebhook($event->id, $rule->id))->handle(app(ISCPPWebhookSender::class));

    Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Basic '.base64_encode('iscpp:secret')));
});

test('the basic auth password is stored encrypted but read back as plaintext', function () {
    $rule = ISCPPEventRule::factory()->create(['basic_auth_password' => 'pw']);

    $raw = DB::table('iscpp_event_rules')->where('id', $rule->id)->first();

    expect($rule->fresh()->basic_auth_password)->toBe('pw')
        ->and($raw->basic_auth_password)->not->toBe('pw');
});

test('unicode is transmitted unescaped so the signature stays reproducible', function () {
    $sender = app(ISCPPWebhookSender::class);

    $encoded = $sender->encode(['name' => 'Matthäus Schmid', 'url' => 'https://a/b']);

    expect($encoded)->toContain('Matthäus')
        ->and($encoded)->toContain('https://a/b');
});

test('a disabled rule short circuits without an http call', function () {
    Http::fake();

    $rule = ISCPPEventRule::factory()->disabled()->create();
    $event = ISCPPEventLog::factory()->create();

    (new DeliverISCPPWebhook($event->id, $rule->id))->handle(app(ISCPPWebhookSender::class));

    Http::assertNothingSent();
    expect(ISCPPEventDelivery::where('status', 'failed')->count())->toBe(1);
});

test('the circuit breaker disables the rule after repeated terminal failures and can be re-enabled', function () {
    $rule = ISCPPEventRule::factory()->create();
    $threshold = (int) config('iscpp.webhook.failure_threshold', 10);

    for ($i = 0; $i < $threshold; $i++) {
        $rule->registerFailure();
    }

    $rule->refresh();
    expect($rule->enabled)->toBeFalse()
        ->and($rule->wasAutoDisabled())->toBeTrue();

    $rule->reenable();
    expect($rule->fresh()->enabled)->toBeTrue()
        ->and($rule->fresh()->consecutive_failures)->toBe(0);
});

test('a success resets the failure counter', function () {
    $rule = ISCPPEventRule::factory()->create(['consecutive_failures' => 3]);

    $rule->registerSuccess();

    expect($rule->fresh()->consecutive_failures)->toBe(0);
});

test('a failing delivery does not raise an ISCPP event', function () {
    Http::fake(['hooks.example.com/*' => Http::response('nope', 500)]);

    $rule = ISCPPEventRule::factory()->create(['url' => 'https://hooks.example.com/fail']);
    $event = ISCPPEventLog::factory()->create();

    $before = ISCPPEventLog::count();
    (new DeliverISCPPWebhook($event->id, $rule->id))->handle(app(ISCPPWebhookSender::class));

    expect(ISCPPEventLog::count())->toBe($before);
});

describe('ssrf guard', function () {
    test('rejects loopback and link local addresses', function (string $url) {
        expect(app(ISCPPWebhookSender::class)->urlRejectionReason($url))->not->toBeNull();
    })->with([
        'http://127.0.0.1/hook',
        'http://localhost/hook',
        'http://169.254.169.254/latest/meta-data',
        'http://192.168.1.10/hook',
        'ftp://example.com/hook',
        'not-a-url',
    ]);

    test('allows a normal public https url', function () {
        expect(app(ISCPPWebhookSender::class)->urlRejectionReason('https://hooks.example.com/x'))->toBeNull();
    });
});
