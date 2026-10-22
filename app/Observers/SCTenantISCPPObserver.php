<?php

namespace App\Observers;

use App\Events\ISCPPSCTenantCreated;
use App\Events\ISCPPSCTenantRemoved;
use App\Models\Event;
use App\Models\SCTenant;
use Illuminate\Support\Facades\Log;

/**
 * Raises ISCPP events for tenant lifecycle changes.
 *
 * Emission is isolated so a failure here can never abort the refresh loop that is
 * creating or deleting the tenant.
 */
class SCTenantISCPPObserver
{
    public function created(SCTenant $sctenant): void
    {
        $this->emit(fn () => ISCPPSCTenantCreated::dispatch($sctenant), $sctenant);
    }

    public function deleted(SCTenant $sctenant): void
    {
        $this->emit(fn () => ISCPPSCTenantRemoved::dispatch($sctenant), $sctenant);
    }

    private function emit(callable $callback, SCTenant $sctenant): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            Log::error('ISCPP tenant event failed', ['tenant' => $sctenant->id, 'error' => $e->getMessage()]);

            try {
                Event::log('iscpp', 'error', ['message' => $e->getMessage(), 'SCTenant' => $sctenant->id]);
            } catch (\Throwable) {
            }
        }
    }
}
