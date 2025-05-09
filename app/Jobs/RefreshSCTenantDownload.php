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

class RefreshSCTenantDownload implements ShouldQueue, ShouldBeUniqueUntilProcessing
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
            Event::log('sctentant-download', 'info', ['message' => 'SCTenantDownload for tenant id ' . $this->tenantID . ' refresh initiated']);
            
            $tenant = SCTenant::find($this->tenantID)->first();
            $data = $scService->tenantDownloads($tenant);
            $tenant->SCTenantDownload()->updateOrCreate(
                ['tenantId' => $tenant],
                [
                    'rawData' => $data,
                    'updated_at' => now(),
                ]
            );
        }
        catch(\Exception $e){
            Event::log('sctentant-download', 'error', ['message' => $e->getMessage()]);
        }

        Event::log('sctentant-download', 'info', ['message' => 'SCTenantDownload refreshed']);   
    }
    
    public function uniqueId(): string
    {
        return 'refresh-sctentant-download-' . $this->tenantID;
    }
}
