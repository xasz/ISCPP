<?php

namespace App\Actions;

use App\Events\ISCPPHealthScoreThresholdDropped;
use App\Events\ISCPPHealthScoreThresholdRecovered;
use App\Models\SCTenant;
use App\Models\SCTenantHealthscore;
use App\Models\SCTenantHealthscoreThreshold;
use App\Services\ISCPPEvents;

/**
 * The single place that decides a healthscore threshold state.
 *
 * It is triggered from both sides, by model observers, so the stored state is always
 * current no matter which code path changed the data:
 *
 * - SCTenantHealthscoreObserver: a tenant's scores were saved, evaluate every score.
 * - SCTenantHealthscoreThresholdObserver: a threshold was created or changed, evaluate
 *   that one score.
 *
 * Every write in here goes through the query builder (or withoutEvents), never through
 * a model save. Otherwise the threshold observer would fire on its own evaluation.
 *
 * Only state transitions raise events, so a score that stays low does not re-notify on
 * every refresh. Transitions are conditional updates that also pin the threshold value
 * they were compared against, which keeps them correct when two refreshes overlap or an
 * admin edits the threshold mid-run.
 */
class EvaluateSCTenantHealthscoreThresholds
{
    /**
     * Evaluate every available score of a tenant.
     */
    public function execute(SCTenant $tenant, ?SCTenantHealthscore $healthscore = null): void
    {
        $healthscore ??= $tenant->SCTenantHealthscore()->first();

        if ($healthscore === null || ! $healthscore->hasScores()) {
            return;
        }

        foreach ($healthscore->getScores() as $scoreKey => $value) {
            $threshold = $this->thresholdFor($tenant, (string) $scoreKey);

            $this->evaluate($tenant, $threshold, $value);
        }
    }

    /**
     * Evaluate a single threshold against the tenant's current score.
     *
     * Used when a threshold is created or its value changes, so the stored state reflects
     * the new threshold immediately instead of waiting for the next healthscore refresh.
     */
    public function executeForThreshold(SCTenantHealthscoreThreshold $threshold): void
    {
        $tenant = $threshold->SCTenant;
        $healthscore = $tenant?->SCTenantHealthscore()->first();

        if ($tenant === null || $healthscore === null || ! $healthscore->hasScores()) {
            return;
        }

        $value = $healthscore->getScores()->get($threshold->score_key, -1);

        $this->evaluate($tenant, $threshold, $value);
    }

    /**
     * Establish the current state without raising events.
     */
    public function seedSilently(SCTenant $tenant, SCTenantHealthscore $healthscore): void
    {
        ISCPPEvents::mute(fn () => $this->execute($tenant, $healthscore));
    }

    private function isUsableScore(mixed $value): bool
    {
        // getScores() walks a dotted path and returns whatever node it lands on, so this
        // guards against Sophos nesting an object where a number used to be.
        return is_int($value) && $value >= 0 && $value <= 100;
    }

    private function thresholdFor(SCTenant $tenant, string $scoreKey): SCTenantHealthscoreThreshold
    {
        // withoutEvents: creating the row here must not trigger the threshold observer,
        // which would evaluate the same score a second time.
        return SCTenantHealthscoreThreshold::withoutEvents(fn () => SCTenantHealthscoreThreshold::firstOrCreate(
            ['tenantId' => $tenant->getKey(), 'score_key' => $scoreKey],
            ['threshold' => SCTenantHealthscoreThreshold::defaultThreshold()],
        ));
    }

    private function evaluate(SCTenant $tenant, SCTenantHealthscoreThreshold $threshold, mixed $value): void
    {
        if (! $this->isUsableScore($value)) {
            // A missing score is not a low score: keep the last known state.
            SCTenantHealthscoreThreshold::whereKey($threshold->getKey())->update([
                'last_evaluated_at' => now(),
            ]);

            return;
        }

        $value = (int) $value;
        $previous = $threshold->last_value;

        SCTenantHealthscoreThreshold::whereKey($threshold->getKey())->update([
            'last_value' => $value,
            'last_evaluated_at' => now(),
        ]);

        if ($value < $threshold->threshold) {
            $this->transitionToLow($tenant, $threshold, $value, $previous);

            return;
        }

        $this->transitionToOk($tenant, $threshold, $value, $previous);
    }

    private function transitionToLow(SCTenant $tenant, SCTenantHealthscoreThreshold $threshold, int $value, ?int $previous): void
    {
        // ok -> low and unknown -> low both fire: a score that is already below its
        // threshold when monitoring starts is a real finding, not a baseline.
        foreach ([SCTenantHealthscoreThreshold::STATE_OK, SCTenantHealthscoreThreshold::STATE_UNKNOWN] as $from) {
            if ($this->changeState($threshold, $from, SCTenantHealthscoreThreshold::STATE_LOW)) {
                ISCPPHealthScoreThresholdDropped::dispatch($tenant, $threshold->fresh(), $value, $previous);

                return;
            }
        }
    }

    private function transitionToOk(SCTenant $tenant, SCTenantHealthscoreThreshold $threshold, int $value, ?int $previous): void
    {
        if ($this->changeState($threshold, SCTenantHealthscoreThreshold::STATE_LOW, SCTenantHealthscoreThreshold::STATE_OK)) {
            ISCPPHealthScoreThresholdRecovered::dispatch($tenant, $threshold->fresh(), $value, $previous);

            return;
        }

        // unknown -> ok is silent. Nothing recovered, and most scores sit at 100, so
        // announcing them would drown the genuine recoveries.
        $this->changeState($threshold, SCTenantHealthscoreThreshold::STATE_UNKNOWN, SCTenantHealthscoreThreshold::STATE_OK);
    }

    /**
     * Atomically move a threshold from one state to another.
     *
     * The threshold value is part of the WHERE clause, so a threshold edited between the
     * comparison and this write results in no transition. The observer on that edit then
     * evaluates again with the new value.
     */
    private function changeState(SCTenantHealthscoreThreshold $threshold, string $from, string $to): bool
    {
        $changed = SCTenantHealthscoreThreshold::whereKey($threshold->getKey())
            ->where('state', $from)
            ->where('threshold', $threshold->threshold)
            ->update([
                'state' => $to,
                'last_changed_at' => now(),
                'updated_at' => now(),
            ]);

        return $changed === 1;
    }
}
