<?php

namespace App\Observers;

use App\Actions\EvaluateSCTenantHealthscoreThresholds;
use App\Models\Event;
use App\Models\SCTenantHealthscoreThreshold;
use Illuminate\Support\Facades\Log;

/**
 * Re-evaluates a threshold as soon as it is created or its value changes.
 *
 * The evaluator writes state through the query builder, so its own writes never reach
 * this observer. Only real threshold edits do.
 */
class SCTenantHealthscoreThresholdObserver
{
    public function __construct(private EvaluateSCTenantHealthscoreThresholds $evaluator) {}

    public function saved(SCTenantHealthscoreThreshold $threshold): void
    {
        if (! $threshold->wasRecentlyCreated && ! $threshold->wasChanged('threshold')) {
            return;
        }

        try {
            $this->evaluator->executeForThreshold($threshold);
        } catch (\Throwable $e) {
            Log::error('Healthscore threshold evaluation failed', ['threshold' => $threshold->id, 'error' => $e->getMessage()]);

            try {
                Event::log('sctentant-healthscore', 'error', [
                    'message' => 'Threshold evaluation failed: '.$e->getMessage(),
                    'SCTenant' => $threshold->tenantId,
                ]);
            } catch (\Throwable) {
            }
        }
    }
}
