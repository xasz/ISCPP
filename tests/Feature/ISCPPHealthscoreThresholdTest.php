<?php

use App\Actions\EvaluateSCTenantHealthscoreThresholds;
use App\Models\ISCPPEventLog;
use App\Models\SCTenant;
use App\Models\SCTenantHealthscore;
use App\Models\SCTenantHealthscoreThreshold;
use App\Models\User;
use Livewire\Volt\Volt;

beforeEach(function () {
    config()->set('iscpp.events.enabled', true);
});

/**
 * Sets every score of the tenant, with the computer protection score set to $score.
 *
 * Saving the healthscore triggers SCTenantHealthscoreObserver, so no explicit
 * evaluation call is made anywhere in this file: that is the behaviour under test.
 */
function setComputerScore(SCTenant $tenant, mixed $score): void
{
    $healthscore = $tenant->SCTenantHealthscore()->first();
    $raw = $healthscore->rawData;

    foreach (SCTenantHealthscore::AvailableScores() as $scoreKey) {
        data_set($raw, 'endpoint.'.$scoreKey, 100);
    }

    data_set($raw, 'endpoint.protection.computer.score', $score);

    $healthscore->update(['rawData' => $raw]);
}

/**
 * A tenant whose scores are all 100 and whose event log is empty.
 *
 * The factory creates random scores, which the observer already evaluates on creation,
 * so they are flattened first to start every test from a known state.
 */
function freshTenant(): SCTenant
{
    $tenant = SCTenant::factory()->create();
    setComputerScore($tenant, 100);
    ISCPPEventLog::query()->delete();

    return $tenant;
}

function computerThreshold(SCTenant $tenant): SCTenantHealthscoreThreshold
{
    return SCTenantHealthscoreThreshold::where('tenantId', $tenant->id)
        ->where('score_key', 'protection.computer.score')
        ->firstOrFail();
}

function lowEvents(): int
{
    return ISCPPEventLog::where('key', 'healthscore.threshold.dropped')->count();
}

function okEvents(): int
{
    return ISCPPEventLog::where('key', 'healthscore.threshold.recovered')->count();
}

describe('on healthscore update', function () {
    test('a score dropping below its threshold stores low and raises the event', function () {
        $tenant = freshTenant();

        setComputerScore($tenant, 40);

        expect(computerThreshold($tenant)->state)->toBe('low')
            ->and(computerThreshold($tenant)->last_value)->toBe(40)
            ->and(lowEvents())->toBe(1);
    });

    test('recovering stores ok and raises the event', function () {
        $tenant = freshTenant();
        setComputerScore($tenant, 40);
        ISCPPEventLog::query()->delete();

        setComputerScore($tenant, 100);

        expect(computerThreshold($tenant)->state)->toBe('ok')
            ->and(okEvents())->toBe(1);
    });

    test('a score that stays low does not repeat the event', function () {
        $tenant = freshTenant();
        setComputerScore($tenant, 40);
        ISCPPEventLog::query()->delete();

        setComputerScore($tenant, 30);
        setComputerScore($tenant, 20);

        expect(lowEvents())->toBe(0)
            ->and(computerThreshold($tenant)->last_value)->toBe(20);
    });

    test('every available score gets a stored state', function () {
        $tenant = freshTenant();

        expect(SCTenantHealthscoreThreshold::where('tenantId', $tenant->id)->count())
            ->toBe(count(SCTenantHealthscore::AvailableScores()))
            ->and(SCTenantHealthscoreThreshold::where('tenantId', $tenant->id)->where('state', 'ok')->count())
            ->toBe(count(SCTenantHealthscore::AvailableScores()));
    });

    test('a threshold defaults to 100 so anything below it is low', function () {
        $tenant = freshTenant();

        setComputerScore($tenant, 99);

        expect(computerThreshold($tenant)->threshold)->toBe(100)
            ->and(computerThreshold($tenant)->state)->toBe('low');
    });

    test('a missing score keeps the last known state and raises nothing', function () {
        $tenant = freshTenant();
        setComputerScore($tenant, 40);
        ISCPPEventLog::query()->delete();

        $healthscore = $tenant->SCTenantHealthscore()->first();
        $raw = $healthscore->rawData;
        unset($raw['endpoint']['protection']['computer']['score']);
        $healthscore->update(['rawData' => $raw]);

        expect(computerThreshold($tenant)->state)->toBe('low')
            ->and(lowEvents() + okEvents())->toBe(0);
    });

    test('a non integer score is treated as missing', function () {
        $tenant = freshTenant();

        setComputerScore($tenant, ['nested' => 'object']);

        expect(computerThreshold($tenant)->state)->toBe('ok')
            ->and(lowEvents() + okEvents())->toBe(0);
    });

    /**
     * Two overlapping refreshes must not both announce the same transition.
     */
    test('a repeated evaluation of the same drop raises exactly one event', function () {
        $tenant = freshTenant();
        setComputerScore($tenant, 30);

        app(EvaluateSCTenantHealthscoreThresholds::class)->execute($tenant);
        app(EvaluateSCTenantHealthscoreThresholds::class)->execute($tenant);

        expect(lowEvents())->toBe(1);
    });
});

describe('on threshold update', function () {
    test('raising a threshold above the current score stores low immediately', function () {
        $tenant = freshTenant();
        setComputerScore($tenant, 90);
        computerThreshold($tenant)->update(['threshold' => 80]);
        ISCPPEventLog::query()->delete();
        expect(computerThreshold($tenant)->state)->toBe('ok');

        computerThreshold($tenant)->update(['threshold' => 95]);

        expect(computerThreshold($tenant)->state)->toBe('low')
            ->and(lowEvents())->toBe(1);
    });

    test('lowering a threshold below the current score stores ok immediately', function () {
        $tenant = freshTenant();
        setComputerScore($tenant, 90);
        ISCPPEventLog::query()->delete();
        expect(computerThreshold($tenant)->state)->toBe('low');

        computerThreshold($tenant)->update(['threshold' => 85]);

        expect(computerThreshold($tenant)->state)->toBe('ok')
            ->and(okEvents())->toBe(1);
    });

    test('lowering a threshold while the score is still at or above it keeps ok', function () {
        $tenant = freshTenant();

        computerThreshold($tenant)->update(['threshold' => 95]);

        expect(computerThreshold($tenant)->state)->toBe('ok')
            ->and(lowEvents() + okEvents())->toBe(0);
    });

    test('saving a threshold without changing its value does not re-evaluate', function () {
        $tenant = freshTenant();
        $threshold = computerThreshold($tenant);
        $evaluatedAt = $threshold->last_evaluated_at;

        $this->travel(5)->minutes();
        $threshold->update(['threshold' => $threshold->threshold]);

        expect(computerThreshold($tenant)->last_evaluated_at?->toDateTimeString())
            ->toBe($evaluatedAt?->toDateTimeString());
    });

    test('saving thresholds on the tenant card evaluates them immediately', function () {
        $this->actingAs(User::factory()->create());
        $tenant = freshTenant();
        setComputerScore($tenant, 90);
        computerThreshold($tenant)->update(['threshold' => 80]);
        ISCPPEventLog::query()->delete();

        // Index 0 is the first entry of AvailableScores(): protection.computer.score.
        Volt::test('sctenant.card-healthscore-thresholds', ['sctenant' => $tenant])
            ->set('thresholds.0.threshold', 95)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('low');

        expect(computerThreshold($tenant)->state)->toBe('low')
            ->and(lowEvents())->toBe(1);
    });
});

test('seeding the baseline stores state without raising events', function () {
    $tenant = freshTenant();

    SCTenantHealthscoreThreshold::where('tenantId', $tenant->id)->update(['state' => 'unknown']);

    $healthscore = $tenant->SCTenantHealthscore()->first();
    $raw = $healthscore->rawData;
    data_set($raw, 'endpoint.protection.computer.score', 20);

    // Store the score without the observer so only the silent seed evaluates it.
    SCTenantHealthscore::withoutEvents(fn () => $healthscore->update(['rawData' => $raw]));

    app(EvaluateSCTenantHealthscoreThresholds::class)->seedSilently($tenant, $healthscore->fresh());

    expect(ISCPPEventLog::count())->toBe(0)
        ->and(computerThreshold($tenant)->state)->toBe('low');
});

test('a failing evaluation does not stop the healthscore from being stored', function () {
    $tenant = freshTenant();

    $this->mock(EvaluateSCTenantHealthscoreThresholds::class)
        ->shouldReceive('execute')
        ->andThrow(new RuntimeException('evaluation broke'));

    $healthscore = $tenant->SCTenantHealthscore()->first();
    $raw = $healthscore->rawData;
    data_set($raw, 'endpoint.protection.computer.score', 10);
    $healthscore->update(['rawData' => $raw]);

    expect(data_get($healthscore->fresh()->rawData, 'endpoint.protection.computer.score'))->toBe(10);
});
