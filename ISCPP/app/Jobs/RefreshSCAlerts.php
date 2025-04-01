<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\SCAlert;
use App\Models\SCTenant;
use App\Services\SCService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RefreshSCAlerts implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Queueable;

    protected $tenantID;

    public function __construct(SCTenant $tenant)
    {
        $this->tenantID = $tenant->id;
    }

    public function handle(SCService $scService): void
    {
        Event::log('scalerts', 'info', ['message' => 'SC Alerts refresh initiated']);
        
        $tenant = SCTenant::find($this->tenantID);

        if(!$tenant){
            Event::log('scalerts', 'error', [
                'message' => 'Tenant not found',
                'TenantID' => $this->tenantID
            ]);
            return;
        }
        
        $from = $tenant->SCAlerts()->orderBy('raisedAt', 'desc')->first()->raisedAt ?? now()->subDays(10);
        Event::logInfo('scalerts', 'Fetching alerts for tenant ' . $tenant->id);
        
        $scService->initialize();
        try{
            $alerts = $scService->alerts($tenant, $from);

        } catch (\Exception $e){
            Event::log('scalerts', 'error', [
                'message' => 'Could not load alerts for tenant',
                'tenant' => $tenant->id,
                'error' => $e->getMessage()
            ]);
            return;
        }
        foreach($alerts as $alert){
            try{
                SCAlert::updateOrCreate(
                    [
                        'id' => $alert['id']
                    ], 
                    [                           
                        'allowedActions' => $alert['allowedActions'],
                        'category' => $alert['category'],
                        'description' => $alert['description'],
                        'groupKey' => $alert['groupKey'],
                        'managedAgentID' => $alert['managedAgent']['id'] ?? null,
                        'managedAgentName' => $alert['managedAgent']['name'] ?? null,
                        'managedAgentType' => $alert['managedAgent']['type'] ?? null,
                        'personID' => $alert['person']['id'] ?? null,
                        'personName' => $alert['person']['name'] ?? null,
                        'product' => $alert['product'],
                        'raisedAt' => $alert['raisedAt'],
                        'severity' => $alert['severity'],
                        'type' => $alert['type'],     
                        'rawData' => json_encode($alert),
                        'tenantId' => $tenant->id
                    ]);
            } catch (\Exception $e){
                Event::log('scalerts', 'error', [
                    'message' => 'Could not save alert',
                    'alert' => $alert['id'],
                    'tenant' => $tenant->id,
                    'rawData' => json_encode($alert),
                    'error' => $e->getMessage()
                ]);
            }
        }
        Event::logInfo('scalerts', $alerts->count() . ' Alerts fetched for tenant ' . $tenant->id);
    }

    public function uniqueId(): string
    {
        return 'refresh-scalerts-' . $this->tenantID;
    }
}
