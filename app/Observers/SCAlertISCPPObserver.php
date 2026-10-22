<?php

namespace App\Observers;

use App\Events\ISCPPSCAlertAcknowledged;
use App\Events\ISCPPSCAlertCreated;
use App\Events\ISCPPSCAlertRemoved;
use App\Models\Event;
use App\Models\SCAlert;
use Illuminate\Support\Facades\Log;

/**
 * Raises ISCPP events for alert lifecycle changes.
 *
 * This observer is registered FIRST, ahead of the auto-acknowledge and legacy webhook
 * observers, for two reasons: SCAlertAcknowledgeAction rethrows on failure and would
 * otherwise prevent this from running at all, and the created payload should describe the
 * alert as it was raised rather than after auto-acknowledge has flipped is_acknowledged.
 *
 * Running first also means an exception here would abort the observers behind it, so
 * emission is wrapped: new code must not be able to break the legacy webhook.
 */
class SCAlertISCPPObserver
{
    public function created(SCAlert $scalert): void
    {
        $this->emit(fn () => ISCPPSCAlertCreated::dispatch($scalert), $scalert);
    }

    /**
     * Only the transition to acknowledged raises an event. Alert refreshes update other
     * columns on every run and must stay silent.
     */
    public function updated(SCAlert $scalert): void
    {
        if (! $scalert->wasChanged('is_acknowledged') || ! $scalert->is_acknowledged) {
            return;
        }

        $this->emit(fn () => ISCPPSCAlertAcknowledged::dispatch($scalert), $scalert);
    }

    public function deleted(SCAlert $scalert): void
    {
        $this->emit(fn () => ISCPPSCAlertRemoved::dispatch($scalert), $scalert);
    }

    private function emit(callable $callback, SCAlert $scalert): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            Log::error('ISCPP alert event failed', ['alert' => $scalert->id, 'error' => $e->getMessage()]);

            try {
                Event::log('iscpp', 'error', ['message' => $e->getMessage(), 'SCAlert' => $scalert->id]);
            } catch (\Throwable) {
            }
        }
    }
}
