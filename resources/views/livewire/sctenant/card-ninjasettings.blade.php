<?php

use Illuminate\Support\Facades\Http;
use Livewire\Volt\Component;

use App\Models\SCTenant;

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
        </div>
    </section>
</x-card>