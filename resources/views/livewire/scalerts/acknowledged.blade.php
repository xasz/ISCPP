
<?php

use App\Actions\SCAlertAcknowledgeAction;
use App\Models\SCAlert;
use Livewire\Volt\Component;

use App\Models\SCTenant;
use App\Services\HaloService;

new class extends Component {
    public SCAlert $scalert;
    public string $errorMessage = '';

    public function mount(SCAlert $scalert)
    {
        $this->scalert = $scalert;
    }

    function acknowledgeAlert(SCAlertAcknowledgeAction $acknowledgeAction)
    {
        $this->errorMessage = '';
        try {
            $acknowledgeAction->execute($this->scalert);
            $this->scalert->refresh();
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
        }
    }
}; ?>

<div>
    <x-card-details-row label="Acknowledged" :value="$scalert->is_acknowledged ? __('Yes') : __('No')" />

    @if($scalert->is_acknowledged == false)
        <x-a-button  wire:loading.attr="disabled" wire:click="acknowledgeAlert">{{ __('Acknowledge') }}</x-a-button>
    @endif
    @if($errorMessage)
        <div class="text-red-600 mt-2">{{ $errorMessage }}</div>
    @endif
    <div wire:loading> 
        Reaching out to Sophos Central ...
    </div>
</div>


