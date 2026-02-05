<?php

use App\Jobs\RefreshSCAlerts;
use App\Models\SCTenant;
use App\Services\SCService;
use Livewire\Volt\Component;
use App\Jobs\RefreshSCTenants;
use App\Models\SCAlert;
use App\Models\SCBillable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

new class extends Component {

    public function alerts(SCService $scService)
    {
        Artisan::call('app:queue-refresh-scalerts-jobs-for-all-tenants');
    }

    
    public function allAlerts(SCService $scService)
    {
        Artisan::call('app:queue-refresh-all-scalerts-jobs-for-all-tenants');
    }

    public function endpoints(SCService $scService)
    {
        Artisan::call('app:queue-refresh-scendpoints-jobs-for-all-tenants');
    }

    
    public function downloads(SCService $scService)
    {
        Artisan::call('app:queue-refresh-downloads-jobs-for-all-tenants');
    }
    
    public function healthscore(SCService $scService)
    {
        Artisan::call('app:queue-refresh-healthscores-jobs-for-all-tenants');
    }

    public function removeBilling(){
        SCBillable::truncate();
    }
    
    public function purgeAlertData(){
        SCAlert::truncate();
    }

    public function runTenantRefresh(SCService $scService)
    {
        Artisan::call('app:queue-sctenants-refresh');
    }
    
    public function dumpPostgresQueue()
    {
        dump(DB::connection(env('DB_CONNECTION'))->table('jobs')->get());
    }
    
    public function dumpSqliteQueue()
    {
        dump(DB::connection('sqlite')->table('jobs')->get());
    }

}; ?>
<x-card title="Running Commands" subtitle="This is for development">
    <flux:text>When you are here - You really should know what you are doing</flux:text>       
        <x-a-button wire:click="alerts">Trigger Refresh Delta Alerts</x-a-button>
        <x-a-button wire:click="alerts">Trigger Refresh ALL Alerts</x-a-button>
        <x-a-button wire:click="endpoints">Trigger Refresh Endpoints</x-a-button>
        <x-a-button wire:click="downloads">Trigger Refresh Downloads</x-a-button>
        <x-a-button wire:click="healthscore">Trigger Refresh Healthscore</x-a-button>
        <x-a-button wire:click="removeBilling">Remove Billing Data</x-a-button>
        <x-a-button wire:click="runTenantRefresh">Trigger Tenant Refresh</x-a-button>
        <x-a-button wire:click="dumpPostgresQueue">dumpPostgresQueue</x-a-button>
        <x-a-button wire:click="dumpSqliteQueue">dumpSqliteQueue</x-a-button>
        <x-a-button wire:click="purgeAlertData">Purge Alert Data</x-a-button>
        <div wire:loading> 
            Loading ...
        </div>
</x-card>