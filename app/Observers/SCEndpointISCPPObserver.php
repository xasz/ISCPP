<?php

namespace App\Observers;

use App\Events\ISCPPSCEndpointCreated;
use App\Events\ISCPPSCEndpointRemoved;
use App\Models\Event;
use App\Models\SCEndpoint;
use Illuminate\Support\Facades\Log;

/**
 * Raises ISCPP events for endpoint lifecycle changes.
 *
 * The removed event is wired but currently dormant: nothing in the application deletes
 * endpoints today, so it only fires once something does.
 */
class SCEndpointISCPPObserver
{
    public function created(SCEndpoint $scendpoint): void
    {
        $this->emit(fn () => ISCPPSCEndpointCreated::dispatch($scendpoint), $scendpoint);
    }

    public function deleted(SCEndpoint $scendpoint): void
    {
        $this->emit(fn () => ISCPPSCEndpointRemoved::dispatch($scendpoint), $scendpoint);
    }

    private function emit(callable $callback, SCEndpoint $scendpoint): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            Log::error('ISCPP endpoint event failed', ['endpoint' => $scendpoint->id, 'error' => $e->getMessage()]);

            try {
                Event::log('iscpp', 'error', ['message' => $e->getMessage(), 'SCEndpoint' => $scendpoint->id]);
            } catch (\Throwable) {
            }
        }
    }
}
