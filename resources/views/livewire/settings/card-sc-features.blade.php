<?php

use Livewire\Volt\Component;
use App\Settings\SCServiceSettings;
use Carbon\Carbon;
use App\Services\ISCPPFormat;

new class extends Component {
    /**
     * Runs on every request of this component, not only on mount.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-settings');
    }

    
    public $message;

    public bool $alertsScheduleEnabled = true;
    public bool $endpointsScheduleEnabled = false;
    public bool $firewallsScheduleEnabled = false;
    public bool $tenantsScheduleEnabled = true;
    public bool $downloadsScheduleEnabled = true;
    public bool $healthscoresScheduleEnabled = true;

    public ?Carbon $lastAlertsSchedule = null;
    public ?Carbon $lastEndpointsSchedule = null;
    public ?Carbon $lastFirewallsSchedule = null;
    public ?Carbon $lastDownloadsSchedule = null;
    public ?Carbon $lastHealthscoresSchedule = null;

    public bool $firewallBackupsScheduleEnabled = false;
    public int $firewallBackupsRetention = 7;
    public ?Carbon $lastFirewallBackupsSchedule = null;

    
    public function mount(SCServiceSettings $settings)
    {
        $this->alertsScheduleEnabled = $settings->alertsScheduleEnabled;
        $this->endpointsScheduleEnabled = $settings->endpointsScheduleEnabled;
        $this->firewallsScheduleEnabled = $settings->firewallsScheduleEnabled;
        $this->downloadsScheduleEnabled = $settings->downloadsScheduleEnabled;
        $this->healthscoresScheduleEnabled = $settings->healthscoresScheduleEnabled;

        $this->lastAlertsSchedule = $settings->lastAlertsSchedule;
        $this->lastEndpointsSchedule = $settings->lastEndpointsSchedule;
        $this->lastFirewallsSchedule = $settings->lastFirewallsSchedule;
        $this->lastDownloadsSchedule = $settings->lastDownloadsSchedule;
        $this->lastHealthscoresSchedule = $settings->lastHealthscoresSchedule;

        $this->firewallBackupsScheduleEnabled = (bool) $settings->firewallBackupsScheduleEnabled;
        $this->firewallBackupsRetention = (int) $settings->firewallBackupsRetention;
        $this->lastFirewallBackupsSchedule = $settings->lastFirewallBackupsSchedule;
    }

    public function updated()
    {
        $this->message = '';
    }


    public function save(SCServiceSettings $settings)
    {
        $this->validate([
            'firewallBackupsRetention' => 'required|integer|min:1|max:365',
        ]);

        $settings->firewallBackupsScheduleEnabled = $this->firewallBackupsScheduleEnabled;
        $settings->firewallBackupsRetention = $this->firewallBackupsRetention;
        $settings->alertsScheduleEnabled = $this->alertsScheduleEnabled;
        $settings->endpointsScheduleEnabled = $this->endpointsScheduleEnabled;
        $settings->firewallsScheduleEnabled = $this->firewallsScheduleEnabled;
        $settings->downloadsScheduleEnabled = $this->downloadsScheduleEnabled;
        $settings->healthscoresScheduleEnabled = $this->healthscoresScheduleEnabled;

        $settings->save();
        $this->message = __('Saved');

        $this->dispatch('featureSet-changed');
    }

}; ?>

<x-card class="w-full2" title="Sophos Central Features" subtitle="Enable ISCPP Sophos Central Features">
    
    <x-card-details-switch  label="Enable Tenants Schedule" checked disabled/>
    <flux:text>
        {{ __('The jobs for updating tenants are autoscheduled every hour') }}
    </flux:text>
    <x-card-hr/>

    <x-card-details-switch  label="Enable Downloads Schedule" wire:model.live="downloadsScheduleEnabled" />
    <flux:text>
        {{ 'Last Run: ' . ($lastDownloadsSchedule != null ? ISCPPFormat::formatDateWithSeconds($lastDownloadsSchedule) : 'never') }}
        {{ __('The jobs for updating downloads are autoscheduled every 6 hours') }}
    </flux:text>
    <x-card-hr/>

    <x-card-details-switch  label="Enable Healthscores Schedule" wire:model.live="healthscoresScheduleEnabled" />
    <flux:text>
        {{ 'Last Run: ' . ($lastHealthscoresSchedule != null ? ISCPPFormat::formatDateWithSeconds($lastHealthscoresSchedule) : 'never') }}
        {{ __('The jobs for updating healthscores are autoscheduled every 6 hours') }}
    </flux:text>
    <x-card-hr/>

    <x-card-details-switch  label="Enable Alerts Schedule" wire:model.live="alertsScheduleEnabled" />
    <flux:text>
        {{ 'Last Run: ' . ($lastAlertsSchedule != null ? ISCPPFormat::formatDateWithSeconds($lastAlertsSchedule) : 'never') }}
        {{ __('The jobs for updating alerts are autoscheduled every hour') }}
    </flux:text>
    <x-card-hr/>

    <x-card-details-switch  label="Enable Endpoints Schedule" wire:model.live="endpointsScheduleEnabled" />
    <flux:text>
        {{ 'Last Run: ' . ($lastEndpointsSchedule != null ? ISCPPFormat::formatDateWithSeconds($lastEndpointsSchedule) : 'never') }}
        {{ __('The jobs for updating endpoints are autoscheduled hour') }}
    </flux:text>    
    <x-card-hr/>

    <x-card-details-switch  label="Enable Firewalls Schedule" wire:model.live="firewallsScheduleEnabled" />
    <flux:text>
        {{ 'Last Run: ' . ($lastFirewallsSchedule != null ? ISCPPFormat::formatDateWithSeconds($lastFirewallsSchedule) : 'never') }}
        {{ __('The jobs for updating firewalls are autoscheduled every hour') }}
    </flux:text>
    <x-card-hr/>

    <x-card-details-switch  label="Enable Firewall Backups Schedule" wire:model.live="firewallBackupsScheduleEnabled" />
    <flux:text>
        {{ 'Last Run: ' . ($lastFirewallBackupsSchedule != null ? ISCPPFormat::formatDateWithSeconds($lastFirewallBackupsSchedule) : 'never') }}
        {{ __('Every connected firewall exports its configuration every night at 02:30. Requires the firewalls schedule.') }}
    </flux:text>
    <div class="w-48">
        <flux:input type="number" min="1" max="365" size="sm" label="{{ __('Backups kept per firewall') }}" wire:model="firewallBackupsRetention" />
    </div>
    @error('firewallBackupsRetention') <flux:text class="text-red-600">{{ $message }}</flux:text> @enderror
    <x-card-hr/>

    <div class="grid justify-items-end mt-4">
        <x-a-button wire:click="save">Save</x-a-button>
    </div>
    <flux:text>
        {{ $message }}
    </flux:text>
</x-card>
