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

class RefreshSCTenantHealthscore implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Queueable;

    protected $tenantID;

    public function __construct(SCTenant $tenant)
    {
        $this->tenantID = $tenant->id;
    }

    public function handle(SCService $scService): void
    {
        try{            
            Event::log('sctentant-healthscore', 'info', ['message' => 'SCTenantHealthscore refresh initiated']);
            
            $tenant = SCTenant::find($this->tenantID)->first();
            $data = $scService->tenantHealthscore($tenant);
            $tenant->SCTenantHealthscore()->updateOrCreate(
                ['tenantId' => $tenant],
                [
                    'rawData' => $data,
                    'updated_at' => now(),
                ]
            );
        }
        catch(\Exception $e){
            Event::log('sctentant-healthscore', 'error', ['message' => $e->getMessage()]);
        }

        Event::log('sctentant-healthscore', 'info', ['message' => 'SCTenantHealthscore refreshed']);   
    }
    
    public function uniqueId(): string
    {
        return 'refresh-sctenants-healthscore-' . $this->tenantID;
    }
}
