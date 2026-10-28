<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\SCEndpoint;
use App\Services\SCService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Refreshes one endpoint out of schedule, e.g. from the endpoint detail page.
 */
class RefreshSCEndpoint implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    public $tries = 2;

    public function __construct(public string $endpointId) {}

    public function handle(SCService $scService): void
    {
        $endpoint = SCEndpoint::with('SCTenant')->find($this->endpointId);

        if ($endpoint === null || $endpoint->SCTenant === null) {
            Event::log('scendpoints', 'error', [
                'message' => 'Endpoint or its tenant not found',
                'endpoint' => $this->endpointId,
            ]);

            return;
        }

        try {
            $data = $scService->endpoint($endpoint->SCTenant, $endpoint->id, 'summary');
        } catch (\Exception $e) {
            Event::log('scendpoints', 'error', [
                'message' => 'Could not load endpoint',
                'endpoint' => $endpoint->id,
                'SCTenant' => $endpoint->tenantId,
                'error' => $e->getMessage(),
            ]);

            return;
        }

        $endpoint->update(SCEndpoint::attributesFromApi($data, $endpoint->tenantId));

        Event::logInfo('scendpoints', 'Endpoint refreshed', ['endpoint' => $endpoint->id, 'SCTenant' => $endpoint->tenantId]);
    }

    public function uniqueId(): string
    {
        return 'refresh-endpoint-'.$this->endpointId;
    }
}
