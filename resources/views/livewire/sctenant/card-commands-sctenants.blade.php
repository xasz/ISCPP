
<?php

use App\Jobs\RefreshSCAlerts;
use App\Jobs\RefreshSCFirewalls;
use App\Models\SCFirewall;
use Livewire\Volt\Component;

use App\Models\SCTenant;
use App\Services\SCService;

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

    public function fetchFirewalls(){
        $this->errorMessage = '';
        try {
            RefreshSCFirewalls::dispatch($this->sctenant, true);
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
        }
    }
}; 
?>

<x-card title="Commands for SCTenant: {{ $sctenant->name }}">
    <x-a-button  wire:loading.attr="disabled" wire:click="dispatchSCAlertsRefresh">{{ __('Dispatch Refresh All Alerts Job') }}</x-a-button>
    <x-a-button  wire:loading.attr="disabled" wire:click="fetchFirewalls">{{ __('Fetch Firewalls') }}</x-a-button>
    @if($errorMessage)
        <div class="text-red-600 mt-2">{{ $errorMessage }}</div>
    @endif
    
    <div wire:loading> 
        Running Command ...
    </div>
</x-card>

