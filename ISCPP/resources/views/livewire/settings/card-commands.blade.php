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
<x-card>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Running Commands (NOW)') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('This is for development') }}
            </p>
        </header>        
        <x-a-button wire:click="alerts">Alerts</x-a-button>
        <x-a-button wire:click="removeBilling">Remove Billing Data</x-a-button>
        <div wire:loading> 
            Loading ...
        </div>
    </section>
</x-card>
