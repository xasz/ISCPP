<?php

use App\Jobs\RefreshSCAlerts;
use App\Models\SCAlert;
use App\Models\SCTenant;
use App\Services\APICrendentialService;
use App\Services\SCService;
use Livewire\Volt\Component;
use App\Models\SCBillable;


new class extends Component {

    public function alerts(SCService $scService)
    {
        $scService->initialize();
        $job = new RefreshSCAlerts(SCTenant::first());
        $job->handle($scService);        
    }

    public function removeBilling(){
        DB::table('sc_billable')->delete();
    }
}; ?>
<x-card title="Running Commands" subtitle="This is for development">
    <flux:text>When you are here - You really should know what you are doing</flux:text>       
        <x-a-button wire:click="alerts">Alerts</x-a-button>
        <x-a-button wire:click="removeBilling">Remove Billing Data</x-a-button>
        <div wire:loading> 
            Loading ...
        </div>
</x-card>
