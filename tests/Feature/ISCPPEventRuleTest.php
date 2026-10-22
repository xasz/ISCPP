<?php

use App\Models\ISCPPEventLog;
use App\Models\ISCPPEventRule;
use App\Models\SCTenant;

beforeEach(function () {
    config()->set('iscpp.events.enabled', true);
});

function ruleFor(array $state = []): ISCPPEventRule
{
    return ISCPPEventRule::factory()->create($state)->load('conditions');
}

test('a rule matches only its own event key', function () {
    $tenantEvent = ISCPPEventLog::factory()->create(['key' => 'sc.tenant.created']);
    $alertEvent = ISCPPEventLog::factory()->create(['key' => 'sc.alert.created']);

    $rule = ruleFor(['event_key' => 'sc.tenant.created']);

    expect($rule->matches($tenantEvent))->toBeTrue()
        ->and($rule->matches($alertEvent))->toBeFalse();
});

test('a disabled rule never matches', function () {
    $event = ISCPPEventLog::factory()->create();

    expect(ruleFor(['enabled' => false])->matches($event))->toBeFalse();
});

describe('tenant scope', function () {
    test('a rule without a tenant matches every tenant', function () {
        $sctenant = SCTenant::factory()->create();
        $event = ISCPPEventLog::factory()->create(['tenantId' => $sctenant->id]);

        expect(ruleFor(['tenantId' => null])->matches($event))->toBeTrue();
    });

    test('a rule without a tenant also matches events with no tenant', function () {
        $event = ISCPPEventLog::factory()->create(['tenantId' => null]);

        expect(ruleFor(['tenantId' => null])->matches($event))->toBeTrue();
    });

    test('a rule with a tenant matches only that tenant', function () {
        $selected = SCTenant::factory()->create();
        $other = SCTenant::factory()->create();

        $rule = ruleFor(['tenantId' => $selected->id]);

        expect($rule->matches(ISCPPEventLog::factory()->create(['tenantId' => $selected->id])))->toBeTrue()
            ->and($rule->matches(ISCPPEventLog::factory()->create(['tenantId' => $other->id])))->toBeFalse();
    });

    test('a rule with a tenant ignores events with no tenant', function () {
        $sctenant = SCTenant::factory()->create();
        $event = ISCPPEventLog::factory()->create(['tenantId' => null]);

        expect(ruleFor(['tenantId' => $sctenant->id])->matches($event))->toBeFalse();
    });
});

describe('conditions', function () {
    function eventWithData(array $data, string $severity = 'info'): ISCPPEventLog
    {
        $event = ISCPPEventLog::factory()->create(['key' => 'sc.alert.created']);
        $payload = $event->payload;
        $payload['data'] = $data;
        $payload['event']['severity'] = $severity;
        $event->update(['payload' => $payload]);

        return $event->fresh();
    }

    function alertRule(): ISCPPEventRule
    {
        return ISCPPEventRule::factory()->forEvent('sc.alert.created')->create();
    }

    test('all conditions must pass', function () {
        $event = eventWithData(['severity' => 'high', 'product' => 'endpoint']);
        $rule = alertRule();
        $rule->conditions()->create(['path' => 'data.severity', 'operator' => 'eq', 'value' => ['high']]);
        $rule->conditions()->create(['path' => 'data.product', 'operator' => 'eq', 'value' => ['firewall']]);

        expect($rule->load('conditions')->matches($event))->toBeFalse();
    });

    test('eq compares as strings so form input matches numeric payloads', function () {
        $event = eventWithData(['value' => 50]);
        $rule = alertRule();
        $rule->conditions()->create(['path' => 'data.value', 'operator' => 'eq', 'value' => ['50']]);

        expect($rule->load('conditions')->matches($event))->toBeTrue();
    });

    test('in matches any listed value', function () {
        $event = eventWithData(['severity' => 'medium']);
        $rule = alertRule();
        $rule->conditions()->create(['path' => 'data.severity', 'operator' => 'in', 'value' => ['high', 'medium']]);

        expect($rule->load('conditions')->matches($event))->toBeTrue();
    });

    test('lt and gte compare numerically', function () {
        $event = eventWithData(['value' => 40]);

        $lt = alertRule();
        $lt->conditions()->create(['path' => 'data.value', 'operator' => 'lt', 'value' => ['50']]);

        $gte = alertRule();
        $gte->conditions()->create(['path' => 'data.value', 'operator' => 'gte', 'value' => ['50']]);

        expect($lt->load('conditions')->matches($event))->toBeTrue()
            ->and($gte->load('conditions')->matches($event))->toBeFalse();
    });

    test('lt fails closed when the value is not numeric', function () {
        $event = eventWithData(['severity' => 'high']);
        $rule = alertRule();
        $rule->conditions()->create(['path' => 'data.severity', 'operator' => 'lt', 'value' => ['50']]);

        expect($rule->load('conditions')->matches($event))->toBeFalse();
    });

    /**
     * The important one: without this, "notify unless severity is high" would fire on
     * every event that has no severity at all.
     */
    test('a missing path never matches, not even with neq', function () {
        $event = eventWithData(['product' => 'endpoint']);

        foreach (['eq', 'neq', 'in', 'lt', 'gte'] as $operator) {
            $rule = alertRule();
            $rule->conditions()->create(['path' => 'data.absent', 'operator' => $operator, 'value' => ['high']]);

            expect($rule->load('conditions')->matches($event))
                ->toBeFalse("operator {$operator} matched a missing path");
        }
    });

    test('conditions can filter on the envelope severity', function () {
        $high = eventWithData(['severity' => 'high'], 'warning');
        $rule = alertRule();
        $rule->conditions()->create(['path' => 'event.severity', 'operator' => 'in', 'value' => ['warning', 'error']]);

        expect($rule->load('conditions')->matches($high))->toBeTrue();
    });
});
