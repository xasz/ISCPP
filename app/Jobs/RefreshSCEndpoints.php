<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\SCEndpoint;
use App\Models\SCTenant;
use App\Services\SCService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RefreshSCEndpoints implements ShouldQueue, ShouldBeUniqueUntilProcessing
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

        if(!$tenant){
            Event::log('scendpoints', 'error', [
                'message' => 'Tenant not found',
                'TenantID' => $this->tenantID
            ]);
            return;
        }
        
        Event::logInfo('scendpoints', 'Fetching endponts for tenant ' . $tenant->id);
        
        try{
            $endpoints = $scService->endpoints($tenant);

        } catch (\Exception $e){
            Event::log('scendpoints', 'error', [
                'message' => 'Could not load endpoints for tenant',
                'tenant' => $tenant->id,
                'error' => $e->getMessage()
            ]);
            return;
        }
        foreach($endpoints as $endpoint){
            try{
                SCEndpoint::updateOrCreate(
                    [
                        'id' => $endpoint['id']
                    ], 
                    [                           
                        'hostname' => $endpoint['hostname'],
                        'tamperProtectionEnabled' => $endpoint['tamperProtectionEnabled'],
                        'lastSeen' => $endpoint['lastSeenAt'],
                        'tenantId' => $tenant->id,
                        'type' => $endpoint['type'],
                        'rawData' => json_encode($endpoint),
                        'healthStatus' => $endpoint['health']['overall'] ?? 'unknown',
                    ]);
            } catch (\Exception $e){
                Event::log('scendpoints', 'error', [
                    'message' => 'Could not save endpoint',
                    'endpoint' => $endpoint['id'],
                    'tenant' => $tenant->id,
                    'rawData' => json_encode($endpoint),
                    'error' => $e->getMessage()
                ]);
            }
        }
        Event::logInfo('scendpoints', $endpoints->count() . ' endpoints fetched for tenant ' . $tenant->id);
    }

    public function uniqueId(): string
    {
        return 'refresh-endpoints-' . $this->tenantID;
    }
}
