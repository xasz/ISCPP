<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\SCEndpoint;
use App\Models\SCTenant;
use App\Services\ISCPPEvents;
use App\Services\SCService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;

class RefreshSCEndpoints implements ShouldBeUniqueUntilProcessing, ShouldQueue
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
        Event::log('scendpoints', 'info', ['message' => 'SC Ednpoints refresh initiated']);

        $tenant = SCTenant::find($this->tenantID);

        if (! $tenant) {
            Event::log('scendpoints', 'error', [
                'message' => 'Tenant not found',
                'SCTenant' => $this->tenantID,
            ]);

            return;
        }

        Event::logInfo('scendpoints', 'Fetching endponts for tenant '.$tenant->id);

        try {
            $endpoints = $scService->endpoints($tenant);

        } catch (\Exception $e) {
            Event::log('scendpoints', 'error', [
                'message' => 'Could not load endpoints for tenant',
                'SCTenant' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return;
        }
        // Endpoint sync is off by default, so the first run after enabling it imports
        // every endpoint at once. That is a backfill, not a stream of new endpoints.
        $isBackfill = $tenant->SCEndpoints()->doesntExist();

        ISCPPEvents::muteIf($isBackfill, function () use ($endpoints, $tenant) {
            $this->importEndpoints($endpoints, $tenant);
        });

        Event::logInfo('scendpoints', $endpoints->count().' endpoints fetched for tenant '.$tenant->id);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $endpoints
     */
    protected function importEndpoints($endpoints, SCTenant $tenant): void
    {
        foreach ($endpoints as $endpoint) {
            try {
                SCEndpoint::updateOrCreate(
                    ['id' => $endpoint['id']],
                    SCEndpoint::attributesFromApi($endpoint, $tenant->id),
                );
            } catch (\Exception $e) {
                Event::log('scendpoints', 'error', [
                    'message' => 'Could not save endpoint',
                    'endpoint' => $endpoint['id'],
                    'SCTenant' => $tenant->id,
                    'rawData' => json_encode($endpoint),
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function uniqueId(): string
    {
        return 'refresh-endpoints-'.$this->tenantID;
    }
}
