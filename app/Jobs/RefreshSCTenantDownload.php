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
    public $tries = 2;

    public function __construct(SCTenant $tenant)
    {
        $this->tenantID = $tenant->id;
    }

    public function handle(SCService $scService): void
    {
        try{            
            Event::log('sctentant-download', 'info', ['message' => 'SCTenantDownload for tenant refresh initiated', 'tenantId' => $this->tenantID]);
            $tenant = SCTenant::findOrFail($this->tenantID);
            $data = $scService->tenantDownloads($tenant);
            $tenant->SCTenantDownload()->updateOrCreate(
                ['tenantId' => $this->tenantID],
                [
                    'rawData' => $data,
                    'updated_at' => now(),
                ]
            );
        }
        catch(\Exception $e){
            Event::log('sctentant-download', 'error', ['message' => $e->getMessage(), 'tenantId' => $this->tenantID]);
        }

        Event::log('sctentant-download', 'info', ['message' => 'SCTenantDownload refreshed', 'tenantId' => $this->tenantID]);   
    }
    
    public function uniqueId(): string
    {
        return 'refresh-sctentant-download-' . $this->tenantID;
    }
}
