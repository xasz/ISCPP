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
    protected $allEvents = false;
    public $tries = 2;

    public function __construct(SCTenant $tenant, $allEvents = false)
    {
        $this->tenantID = $tenant->id;
        $this->allEvents = $allEvents;
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
        
        $from = $tenant->SCAlerts()->orderBy('raisedAt', 'desc')->first()->raisedAt ?? now()->subDays(9999);
        if($this->allEvents){
            $from = now()->subDays(9999);
        }
        Event::logInfo('scalerts', 'Fetching alerts for tenant ' . $tenant->id . ' from ' . $from->toDateTimeString());
        
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

                $data = SCAlert::find($alert['id']);
                $target = [                           
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
                        'tenantId' => $tenant->id,
                        'updated_at' => now(),
                ];

                if($data){
                    $data->update($target);
                    continue;
                }

                SCAlert::updateOrCreate(
                    [
                        'id' => $alert['id']
                    ], 
                    $target
                );

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
