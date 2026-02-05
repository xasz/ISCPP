
<?php

use App\Jobs\RefreshSCAlerts;
use Livewire\Volt\Component;

use App\Models\SCTenant;

new class extends Component {
    
    public $sctenant;
    public string $errorMessage = '';

    public function mount(SCTenant $sctenant)
    {
        $this->sctenant = $sctenant;
    }

    public function dispatchSCAlertsRefresh(){
        $this->errorMessage = '';
        try {
            RefreshSCAlerts::dispatch($this->sctenant, true);
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
        }
    }
}; ?>

<x-card title="Commands for SCTenant: {{ $sctenant->name }}">
    <x-a-button  wire:loading.attr="disabled" wire:click="dispatchSCAlertsRefresh">{{ __('Dispatch Refresh All Alerts Job') }}</x-a-button>
    @if($errorMessage)
        <div class="text-red-600 mt-2">{{ $errorMessage }}</div>
    @endif
    
    <div wire:loading> 
        Running Command ...
    </div>
</x-card>

