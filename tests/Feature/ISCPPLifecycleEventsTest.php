<?php

use App\Actions\SCAlertAcknowledgeAction;
use App\Jobs\RefreshSCFirewalls;
use App\Models\ISCPPEventLog;
use App\Models\ISCPPEventRule;
use App\Models\SCAlert;
use App\Models\SCFirewall;
use App\Models\SCTenant;
use App\Models\SCTenantHealthscore;
use App\Services\SCService;
use Illuminate\Support\Str;

beforeEach(function () {
    config()->set('iscpp.events.enabled', true);
});

function eventsOfKey(string $key): int
{
    return ISCPPEventLog::where('key', $key)->count();
}

describe('alerts', function () {
    test('acknowledging an alert raises the acknowledged event', function () {
        $tenant = SCTenant::factory()->create();
        $alert = SCAlert::factory()->create(['tenantId' => $tenant->id, 'is_acknowledged' => false]);

        $alert->update(['is_acknowledged' => true]);

        $event = ISCPPEventLog::where('key', 'sc.alert.acknowledged')->first();

        expect($event)->not->toBeNull()
            ->and($event->subject_id)->toBe($alert->id)
            ->and($event->tenantId)->toBe($tenant->id);
    });

    test('the acknowledge action raises the acknowledged event', function () {
        $tenant = SCTenant::factory()->create();
        $alert = SCAlert::factory()->create(['tenantId' => $tenant->id, 'is_acknowledged' => false]);

        $this->mock(SCService::class)->shouldReceive('alertsAction')->once();

        app(SCAlertAcknowledgeAction::class)->execute($alert);

        expect(eventsOfKey('sc.alert.acknowledged'))->toBe(1);
    });

    test('an alert refresh that does not touch acknowledgement stays silent', function () {
        $tenant = SCTenant::factory()->create();
        $alert = SCAlert::factory()->create(['tenantId' => $tenant->id, 'is_acknowledged' => false]);

        $alert->update(['description' => 'Updated by refresh']);

        expect(eventsOfKey('sc.alert.acknowledged'))->toBe(0);
    });

    test('saving an already acknowledged alert does not repeat the event', function () {
        $tenant = SCTenant::factory()->create();
        $alert = SCAlert::factory()->create(['tenantId' => $tenant->id, 'is_acknowledged' => true]);

        $alert->update(['description' => 'Updated by refresh']);

        expect(eventsOfKey('sc.alert.acknowledged'))->toBe(0);
    });

    test('deleting an alert raises the removed event', function () {
        $tenant = SCTenant::factory()->create();
        $alert = SCAlert::factory()->create(['tenantId' => $tenant->id]);

        $alert->delete();

        expect(eventsOfKey('sc.alert.removed'))->toBe(1);
    });
});

describe('firewalls', function () {
    test('creating a firewall raises the created event with its name', function () {
        $tenant = SCTenant::factory()->create();

        $firewall = SCFirewall::factory()->create(['tenantId' => $tenant->id]);

        $event = ISCPPEventLog::where('key', 'sc.firewall.created')->first();

        expect($event)->not->toBeNull()
            ->and($event->payload['data']['firewallId'])->toBe($firewall->id)
            ->and($event->payload['data']['name'])->toBe($firewall->rawData['name']);
    });

    test('deleting a firewall raises the removed event', function () {
        $tenant = SCTenant::factory()->create();
        $firewall = SCFirewall::factory()->create(['tenantId' => $tenant->id]);

        $firewall->delete();

        expect(eventsOfKey('sc.firewall.removed'))->toBe(1);
    });

    test('the first firewall sync of a tenant is not announced, later new firewalls are', function () {
        $tenant = SCTenant::factory()->create();

        $firstId = (string) Str::uuid();
        $secondId = (string) Str::uuid();

        $sync = function (array $ids) use ($tenant) {
            $service = Mockery::mock(SCService::class);
            $service->shouldReceive('firewalls')->andReturn(collect($ids)->map(fn (string $id) => [
                'id' => $id,
                'name' => 'FW '.$id,
                'hostname' => 'fw-'.$id,
            ])->all());

            (new RefreshSCFirewalls($tenant))->handle($service);
        };

        $sync([$firstId]);
        expect(eventsOfKey('sc.firewall.created'))->toBe(0);

        $sync([$firstId, $secondId]);
        expect(eventsOfKey('sc.firewall.created'))->toBe(1);
    });
});

describe('healthscores', function () {
    test('the first healthscore of a tenant raises the created event', function () {
        $tenant = SCTenant::factory()->create();

        $event = ISCPPEventLog::where('key', 'healthscore.created')->where('tenantId', $tenant->id)->first();

        expect($event)->not->toBeNull()
            ->and($event->payload['data']['scores'])->toHaveKey('protection.computer.score');
    });

    test('updating a healthscore does not raise the created event again', function () {
        $tenant = SCTenant::factory()->create();
        ISCPPEventLog::query()->delete();

        $healthscore = $tenant->SCTenantHealthscore()->first();
        $healthscore->update(['rawData' => $healthscore->rawData]);

        expect(eventsOfKey('healthscore.created'))->toBe(0);
    });

    test('missing scores are reported as null', function () {
        $tenant = SCTenant::factory()->make();
        SCTenant::withoutEvents(fn () => $tenant->saveQuietly());
        ISCPPEventLog::query()->delete();

        SCTenantHealthscore::create([
            'tenantId' => $tenant->id,
            'rawData' => ['endpoint' => ['protection' => ['computer' => ['score' => 90]]]],
        ]);

        $scores = ISCPPEventLog::where('key', 'healthscore.created')->first()->payload['data']['scores'];

        expect($scores['protection.computer.score'])->toBe(90)
            ->and($scores['protection.server.score'])->toBeNull();
    });

    test('a new healthscore announces itself before any threshold event it causes', function () {
        $tenant = SCTenant::factory()->make();
        SCTenant::withoutEvents(fn () => $tenant->saveQuietly());
        ISCPPEventLog::query()->delete();

        SCTenantHealthscore::create([
            'tenantId' => $tenant->id,
            'rawData' => ['endpoint' => ['protection' => ['computer' => ['score' => 40]]]],
        ]);

        $keys = ISCPPEventLog::orderBy('id')->pluck('key')->all();

        expect($keys[0])->toBe('healthscore.created')
            ->and($keys)->toContain('healthscore.threshold.dropped');
    });

    test('deleting a healthscore raises the removed event', function () {
        $tenant = SCTenant::factory()->create();
        ISCPPEventLog::query()->delete();

        $tenant->SCTenantHealthscore()->first()->delete();

        expect(eventsOfKey('healthscore.removed'))->toBe(1);
    });
});

test('the key rename migration moves rules and recorded events to the new healthscore keys', function () {
    $rule = ISCPPEventRule::factory()->create(['event_key' => 'healthscore.low']);
    $event = ISCPPEventLog::factory()->create(['key' => 'healthscore.ok']);

    $migration = require database_path('migrations/2026_09_15_065330_rename_iscpp_healthscore_event_keys.php');
    $migration->up();

    $event->refresh();

    expect($rule->fresh()->event_key)->toBe('healthscore.threshold.dropped')
        ->and($event->key)->toBe('healthscore.threshold.recovered')
        ->and($event->payload['event']['key'])->toBe('healthscore.threshold.recovered')
        ->and($event->payload['event']['label'])->toBe('Healthscore Threshold Recovered');
});
