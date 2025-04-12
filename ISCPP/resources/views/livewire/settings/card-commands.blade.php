<?php

use App\Jobs\RefreshSCAlerts;
use App\Models\SCTenant;
use App\Services\SCService;
use Livewire\Volt\Component;
use App\Jobs\RefreshSCTenants;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

new class extends Component {

    public function alerts(SCService $scService)
    {
        Artisan::call('app:queue-refresh-scalerts-jobs-for-all-tenants');
    }

    public function endpoints(SCService $scService)
    {
        Artisan::call('app:queue-refresh-scendpoints-jobs-for-all-tenants');
    }
    public function removeBilling(){
        DB::table('sc_billable')->delete();
    }

    public function runTenantRefresh(SCService $scService)
    {
        RefreshSCTenants::dispatch();
    }
}; ?>
<x-card title="Running Commands" subtitle="This is for development">
    <flux:text>When you are here - You really should know what you are doing</flux:text>       
        <x-a-button wire:click="alerts">Trigger Refresh Alerts</x-a-button>
        <x-a-button wire:click="endpoints">Trigger Refresh Endpoints</x-a-button>
        <x-a-button wire:click="removeBilling">Remove Billing Data</x-a-button>
        <div>
            <x-a-button wire:click="runTenantRefresh">Trigger Tenant Refresh (Automatically Done every 15 Minutes)</x-a-button>
        </div>
        <div wire:loading> 
            Loading ...
        </div>
</x-card>
