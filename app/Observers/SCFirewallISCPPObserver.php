<?php

namespace App\Observers;

use App\Events\ISCPPSCFirewallCreated;
use App\Events\ISCPPSCFirewallRemoved;
use App\Models\Event;
use App\Models\SCFirewall;
use Illuminate\Support\Facades\Log;

/**
 * Raises ISCPP events for firewall lifecycle changes.
 *
 * The removed event is wired but currently dormant: nothing in the application deletes
 * firewalls today, so it only fires once something does.
 */
class SCFirewallISCPPObserver
{
    public function created(SCFirewall $scfirewall): void
    {
        $this->emit(fn () => ISCPPSCFirewallCreated::dispatch($scfirewall), $scfirewall);
    }

    public function deleted(SCFirewall $scfirewall): void
    {
        $this->emit(fn () => ISCPPSCFirewallRemoved::dispatch($scfirewall), $scfirewall);
    }

    private function emit(callable $callback, SCFirewall $scfirewall): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            Log::error('ISCPP firewall event failed', ['firewall' => $scfirewall->id, 'error' => $e->getMessage()]);

            try {
                Event::log('iscpp', 'error', ['message' => $e->getMessage(), 'SCTenant' => $scfirewall->tenantId]);
            } catch (\Throwable) {
            }
        }
    }
}
