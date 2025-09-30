<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\SCAlert;
use App\Models\SCTenant;
use App\Services\SCService;
use App\Services\WebhookService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RefreshSCTenants implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Queueable;
    public $tries = 2;

    public function handle(SCService $scService): void
    {
        try{            
            Event::log('sctentants', 'info', ['message' => 'SC Tenants refresh initiated']);
            
            $tenants = $scService->tenants();        
            $ids = $tenants->map(function ($tenant) {
                return $tenant['id'];
            });

            Event::logInfo('sctentants', 'Fetched ' . $tenants->count() . ' tenants');
            $tenants->each(function ($tenant) {
                SCTenant::updateOrCreate(['id' => $tenant['id']], 
                    [
                        'showAs' => $tenant['showAs'] ?? null,
                        'name' => $tenant['name'] ?? null,
                        'dataGeography' => $tenant['dataGeography'] ?? null,
                        'dataRegion' => $tenant['dataRegion'] ?? null,
                        'billingType' => $tenant['billingType'] ?? null,
                        'partnerId' => $tenant['partnerId'] ?? null,
                        'organizationId' => $tenant['organizationId'] ?? null,
                        'apiHost' => $tenant['apiHost'] ?? null,
                        'rawData' => json_encode($tenant),
                        'updated_at' => now(),
                    ]
                );
            });       
            $count = SCTenant::whereNotIn('id', $ids)->delete();
            Event::log('sctentants', 'info', ['message' => 'Deleted ' . $count . ' tenants']);            
        }
        catch(\Exception $e){
            Event::log('sctentants', 'error', ['message' => $e->getMessage()]);
        }

        Event::log('sctentants', 'info', ['message' => 'Tenants refreshed']);   
    }
    
    public function uniqueId(): string
    {
        return 'refresh-sctenants';
    }
}
