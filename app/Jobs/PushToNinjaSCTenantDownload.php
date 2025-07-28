<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\SCBillable;
use App\Models\SCTenant;
use App\Services\HaloService;
use App\Services\NinjaService;
use App\Settings\NinjaServiceSettings;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Throwable;

class PushToNinjaSCTenantDownload implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Queueable;

    public $tries = 1;

    public function __construct(
        public SCTenant $sctenant
    ) {
        $this->sctenant = $sctenant->withoutRelations();
    }

    /**
     * Execute the job.
     */
    public function handle(NinjaService $ninjaService): void
    {
        Event::log("SCTenantDownload", "info" , [
            'message' => 'Start sending SCBillables for SCTenant to Halo',
            'SCTenantID' => $this->sctenant->id
        ]);

        if($this->sctenant->ninjaorg_id == -1){
            Event::log("SCTenantDownload", "error" , [
                'message' => __('SCTenantDownload sent to ninja failed - no ninjaorg_id'),
                'SCTenantID' => $this->sctenant->id
            ]);
            $this->release(120);
            return;
        }

        $ninjaService->patchOrganizationCustomField(
            $this->sctenant->ninjaorg_id,
            'windowsSophosCentralEndpointInstallerUrl',
            $this->sctenant->SCTenantDownload->getWindowsInstallerUrl()
        );

        Event::log("SCTenantDownload", "info" , [
            'message' => __('SCTenantDownload pushed successfull'),
            'SCTenantID' => $this->sctenant->id,
        ]);
    }

    public function failed(Throwable $exception): void
    {                 
        $settings = app(NinjaServiceSettings::class);
        Event::log("scbillables", "error" , [
            'message' => __('SCBillable sent to halo failed - no more retry'),
            'SCTenantID' => $this->sctenant->id,
            'ninjaorg_id' => $this->sctenant->ninjaorg_id,
            'ninjaOne_windowsurl_targetfield' => $settings->windowsSophosCentralEndpointInstallerUrl,
            'ninjaOne_windowsUrl_targetvalue' => $this->sctenant->SCTenantDownload->getWindowsInstallerUrl(),
        ]);    
    }

    public function uniqueId(): string
    {
        return $this->sctenant->id;
    }
}
