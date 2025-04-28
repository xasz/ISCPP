<?php

use App\Services\APICrendentialService;
use App\Services\SCService;
use Livewire\Volt\Component;
use App\Settings\SCServiceSettings;
use Carbon\Carbon;


new class extends Component {
    
    public $message;

    public bool $alertsScheduleEnabled = true;
    public bool $endpointsScheduleEnabled = false;
    public bool $firewallsScheduleEnabled = false;

    public ?Carbon $lastAlertsSchedule = null;
    public ?Carbon $lastEndpointsSchedule = null;
    public ?Carbon $lastFirewallsSchedule = null;

    
    public function mount(SCServiceSettings $settings)
    {
        $this->alertsScheduleEnabled = $settings->alertsScheduleEnabled;
        $this->endpointsScheduleEnabled = $settings->endpointsScheduleEnabled;
        $this->firewallsScheduleEnabled = $settings->firewallsScheduleEnabled;

        $this->lastAlertsSchedule = $settings->lastAlertsSchedule;
        $this->lastEndpointsSchedule = $settings->lastEndpointsSchedule;
        $this->lastFirewallsSchedule = $settings->lastFirewallsSchedule;

    }

    public function updated()
    {
        $this->message = '';
    }


    public function save(SCServiceSettings $settings)
    {
        $settings->alertsScheduleEnabled = $this->alertsScheduleEnabled;
        $settings->endpointsScheduleEnabled = $this->endpointsScheduleEnabled;
        $settings->firewallsScheduleEnabled = $this->firewallsScheduleEnabled;
        $settings->save();
        $this->message = __('Saved - Please reload the page if you are missing menus');

        $this->dispatch('featureSet-changed');
    }

}; ?>

<x-card class="w-1/2" title="Sophos Central Features" subtitle="Enable ISCPP Sophos Central Features">
    
    <x-card-details-switch  label="Enable Tenants Schedule" checked disabled/>
    <flux:text>
        {{ __('The jobs for updating tenants are autoscheduled every 30 minutes') }}
    </flux:text>

    <x-card-details-switch  label="Enable Alerts Schedule" wire:model.live="alertsScheduleEnabled" />
    <flux:text>
        {{ 'Last Run: ' . ($lastAlertsSchedule != null ? $lastAlertsSchedule->toString() : 'never') }}
        {{ __('The jobs for updating alerts are autoscheduled every 15 minutes') }}
    </flux:text>

    <x-card-details-switch  label="Enable Endpoints Schedule" wire:model.live="endpointsScheduleEnabled" />
    <flux:text>
        {{ 'Last Run: ' . ($lastEndpointsSchedule != null ? $lastEndpointsSchedule->toString() : 'never') }}
        {{ __('The jobs for updating endpoints are autoscheduled every 30 minutes') }}
    </flux:text>
    
    <x-card-details-switch  label="Enable Firewalls Schedule" wire:model.live="firewallsScheduleEnabled" />
    <flux:text>
        {{ 'Last Run: ' . ($lastFirewallsSchedule != null ? $lastFirewallsSchedule->toString() : 'never') }}
        {{ __('The jobs for updating firewalls are autoscheduled every 30 minutes') }}
    </flux:text>

    <div class="grid justify-items-end mt-4">
        <x-a-button wire:click="save">Save</x-a-button>
    </div>
    <flux:text>
        {{ $message }}
    </flux:text>
</x-card>
