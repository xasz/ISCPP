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
    public function handle(NinjaService $ninjaService, NinjaServiceSettings $ninjaSettings): void
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

        $fields = [];

        if($ninjaSettings->windowsSophosCentralEndpointInstallerUrl && $ninjaSettings->windowsSophosCentralEndpointInstallerUrl != '') {
            $fields[$ninjaSettings->windowsSophosCentralEndpointInstallerUrl] = $this->sctenant->SCTenantDownload->getWindowsInstallerUrl();
        }

        if($ninjaSettings->linuxSophosCentralEndpointInstallerUrl && $ninjaSettings->linuxSophosCentralEndpointInstallerUrl != '') {
            $fields[$ninjaSettings->linuxSophosCentralEndpointInstallerUrl] = $this->sctenant->SCTenantDownload->getLinuxInstallerUrl();
        }

        if($ninjaSettings->macSophosCentralEndpointInstallerUrl && $ninjaSettings->macSophosCentralEndpointInstallerUrl != '') {
            $fields[$ninjaSettings->macSophosCentralEndpointInstallerUrl] = $this->sctenant->SCTenantDownload->getMacOSInstallerUrl();
        }

        $ninjaService->patchOrganizationCustomFields($this->sctenant->ninjaorg_id, $fields);


        Event::log("SCTenantDownload", "info" , [
            'message' => __('Pushed successfull'),
            'SCTenantID' => $this->sctenant->id,
            'ninjaorg_id' => $this->sctenant->ninjaorg_id,
            'fields' => $fields,
        ]);

    }

    public function failed(Throwable $exception): void
    {                 
        $ninjaSettings = app(NinjaServiceSettings::class);
        Event::log("SCTenantDownload", "error" , [
            'message' => __('SCTenantDownload Push to Ninja failed- no more retry'),
            'SCTenantID' => $this->sctenant->id,
            'ninjaorg_id' => $this->sctenant->ninjaorg_id,
            'exception' => $exception->getMessage(),
            'field' => $ninjaSettings->windowsSophosCentralEndpointInstallerUrl            
        ]);    
    }

    public function uniqueId(): string
    {
        return $this->sctenant->id;
    }
}
