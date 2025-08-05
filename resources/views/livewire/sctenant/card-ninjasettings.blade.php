<?php

use App\Jobs\PushToNinjaSCTenantDownload;
use Illuminate\Support\Facades\Http;
use Livewire\Volt\Component;

use App\Models\SCTenant;
use App\Services\NinjaService;
use App\Settings\NinjaServiceSettings;

new class extends Component {
    
    public $orgId;
    public $message;
    public $sctenant;

    public function mount(SCTenant $sctenant)
    {
        $this->sctenant = $sctenant;
        $this->orgId = $sctenant->ninjaorg_id;
    }


    public function save()
    {
        # check if clientID is an integer
        if (!is_numeric($this->orgId)) {
            $this->message = __('Org ID must be an integer');
            return;
        }
        $this->sctenant->update([
            'ninjaorg_id' => $this->orgId,
        ]);        
        $this->message = __('Saved');        
    }

    public function pushDownloadsToNinjaOne(NinjaService $ninjaService, NinjaServiceSettings $ninjaServiceSettings)
    {

        $sctenant = $this->sctenant;
        try {
            $job = new PushToNinjaSCTenantDownload($sctenant);
            dispatch($job);
            $this->message = __('Job scheduled successfully - See Eventlog for details');
        } catch (Exception $e) {
            $this->message = __('Failed to push to NinjaOne: ') . $e->getMessage();
        }
    }
}; ?>

<x-card>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('NinjaOne Integration Settings') }}
            </h2>
        </header>
        
        <div class="relative overflow-x-auto mt-2">
            <x-card-details-input label="Ninjaone Organization ID" wire:model="orgId"/>
            <div class="grid justify-items-end mt-4">
                <x-a-button wire:click="save">{{ __('Save') }}</x-a-button>
            </div>
            <div class="py-2">
                {{ $message }}
            <div>

            <x-button wire:click="pushDownloadsToNinjaOne" class="mt-4">
                {{ __('Schedule Push to NinjaOne') }}
            </x-button>
        </div>
    </section>
</x-card>