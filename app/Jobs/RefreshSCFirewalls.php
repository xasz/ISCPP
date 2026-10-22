<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\SCFirewall;
use App\Models\SCTenant;
use App\Services\ISCPPEvents;
use App\Services\SCService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RefreshSCFirewalls implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    protected $tenantID;

    public $tries = 2;

    public function __construct(SCTenant $tenant)
    {
        $this->tenantID = $tenant->id;
    }

    public function handle(SCService $scService): void
    {
        Event::log('scfirewalls', 'info', ['message' => 'SC Firewalls refresh initiated']);

        $tenant = SCTenant::find($this->tenantID);

        if (! $tenant) {
            Event::log('scfirewalls', 'error', [
                'message' => 'Tenant not found',
                'SCTenant' => $this->tenantID,
            ]);

            return;
        }

        Event::logInfo('scfirewalls', 'Fetching firewalls for tenant '.$tenant->id);

        try {
            $firewalls = $scService->firewalls($tenant);
        } catch (\Exception $e) {
            Event::log('scfirewalls', 'error', [
                'message' => 'Could not load firewalls for tenant',
                'SCTenant' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return;
        }
        // The first firewall sync of a tenant imports every firewall at once. That is a
        // backfill, not a stream of new firewalls, so it stays out of the event log.
        $isBackfill = $tenant->SCFirewalls()->doesntExist();

        ISCPPEvents::muteIf($isBackfill, function () use ($firewalls, $tenant) {
            $this->importFirewalls($firewalls, $tenant);
        });

        Event::logInfo('scfirewalls', count($firewalls).' firewalls fetched for tenant '.$tenant->id);
    }

    /**
     * @param  iterable<int, array<string, mixed>>  $firewalls
     */
    protected function importFirewalls(iterable $firewalls, SCTenant $tenant): void
    {
        foreach ($firewalls as $firewall) {
            try {
                SCFirewall::updateOrCreate(
                    [
                        'id' => $firewall['id'],
                    ],
                    [
                        'name' => $firewall['name'],
                        'hostname' => $firewall['hostname'],
                        'rawData' => $firewall,
                        'tenantId' => $tenant->id,
                    ]);
            } catch (\Exception $e) {
                Event::log('scfirewalls', 'error', [
                    'message' => 'Could not save firewall for tenant',
                    'firewall' => $firewall['id'],
                    'SCTenant' => $tenant->id,
                    'rawData' => json_encode($firewall),
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function uniqueId(): string
    {
        return 'refresh-firewalls-'.$this->tenantID;
    }
}
