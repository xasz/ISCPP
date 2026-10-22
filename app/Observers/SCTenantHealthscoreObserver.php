<?php

namespace App\Observers;

use App\Actions\EvaluateSCTenantHealthscoreThresholds;
use App\Events\ISCPPHealthScoreCreated;
use App\Events\ISCPPHealthScoreRemoved;
use App\Models\Event;
use App\Models\SCTenantHealthscore;
use Illuminate\Support\Facades\Log;

/**
 * Raises healthscore lifecycle events and re-evaluates thresholds on every save.
 *
 * Bound to the model rather than the refresh job, so every path that stores scores
 * (scheduled refresh, manual refresh, seeders) keeps the threshold state current.
 *
 * Eloquent fires created before saved, so a tenant's first healthscore announces itself
 * before any threshold event that the same save produces.
 */
class SCTenantHealthscoreObserver
{
    public function __construct(private EvaluateSCTenantHealthscoreThresholds $evaluator) {}

    public function created(SCTenantHealthscore $healthscore): void
    {
        $this->guard($healthscore, 'Healthscore created event failed', fn () => ISCPPHealthScoreCreated::dispatch($healthscore));
    }

    public function saved(SCTenantHealthscore $healthscore): void
    {
        $this->guard($healthscore, 'Threshold evaluation failed', function () use ($healthscore) {
            if ($healthscore->SCTenant !== null) {
                $this->evaluator->execute($healthscore->SCTenant, $healthscore);
            }
        });
    }

    public function deleted(SCTenantHealthscore $healthscore): void
    {
        $this->guard($healthscore, 'Healthscore removed event failed', fn () => ISCPPHealthScoreRemoved::dispatch($healthscore));
    }

    /**
     * Never let events or threshold evaluation break storing the healthscore itself.
     */
    private function guard(SCTenantHealthscore $healthscore, string $message, callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            Log::error($message, ['tenant' => $healthscore->tenantId, 'error' => $e->getMessage()]);

            try {
                Event::log('sctentant-healthscore', 'error', [
                    'message' => $message.': '.$e->getMessage(),
                    'SCTenant' => $healthscore->tenantId,
                ]);
            } catch (\Throwable) {
            }
        }
    }
}
