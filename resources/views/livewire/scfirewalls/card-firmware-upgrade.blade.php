<?php

use App\Models\SCFirewall;
use Carbon\Carbon;
use Livewire\Volt\Component;
use Illuminate\Support\Collection;

new class extends Component {
    public SCFirewall $firewall;

    public array $availableVersions = [];    
    public array $firmwareVersions = [];
    public string $selectedVersion = '';
    public string $currentVersion = '';
    

    
    public string $upgradeAt = '';
    public string $errorMessage = '';

    public bool $checked = false;

    public function mount(SCFirewall $firewall): void
    {
        $this->firewall = $firewall;
        $this->upgradeAt = now()->addMinutes(120*24)->format('Y-m-d\TH:i');
    }

    public function check(): void
    {
        $this->errorMessage = '';
        $this->checked = true;

        try{
            $response = $this->firewall->checkFirmwareUpgrade();
        }catch (Throwable $exception) {
            $this->errorMessage = $exception->getMessage();
            return;
        };
        $firewallInfo = collect(collect($response['firewalls'])->where('id', $this->firewall->id)->first());

        $this->availableVersions = $firewallInfo->get('upgradeToVersion', []);
        $this->firmwareVersions = $response['firmwareVersions'];
        $this->currentVersion = $firewallInfo->get('firmwareVersion', '');
        $this->selectedVersion = '';

    }

    public function plan(): void
    {
        $this->errorMessage = '';

        if ($this->selectedVersion === '') {
            $this->errorMessage = __('Select a firmware version first.');

            return;
        }

        $upgradeAt = Carbon::createFromFormat('Y-m-d\TH:i', $this->upgradeAt, config('app.timezone'));

        $response = $this->firewall->scheduleFirmwareUpgrade(
            $this->selectedVersion,
            $upgradeAt,
        );

        if (isset($response['error'])) {
            $this->errorMessage = (string) ($response['message'] ?? $response['error']);

            return;
        }

        $this->firewall->update([
            'plannedFirmwareUpgradeAt' => $this->upgradeAt,
        ]);

        $this->message = __('Firmware upgrade scheduled successfully.');
    }

    public function cancel(): void
    {
        $this->errorMessage = '';

        $response = $this->firewall->cancelFirmwareUpgrade();
        $isDeleted = data_get($response, 'deleted', false);

        if($isDeleted === false){

        }

        if (isset($response['error'])) {
            $this->errorMessage = (string) ($response['message'] ?? $response['error']);
        }

        $this->firewall->update([
            'plannedFirmwareUpgradeAt' => null,
        ]);
    }

    public function getSelectedVersionDetailsProperty(): array
    {
        if ($this->selectedVersion === '') {
            return [];
        }

        return collect($this->availableVersions)
            ->firstWhere('version', $this->selectedVersion) ?? [];
    }
}; ?>



<div class="flex flex-col gap-4">

    @if($firewall->plannedFirmwareUpgradeAt == null || $firewall->plannedFirmwareUpgradeAt->isBefore(now()))
        <x-card title="Firmware Upgrade" subtitle="Check available Sophos Firewall firmware upgrades and schedule an upgrade plan.">
            <div>
                <x-card-details-row label="Current Firmware" :value="data_get($firewall->rawData, 'firmwareVersion', __('N/A'))" />
                <x-card-details-row label="Serial Number" :value="data_get($firewall->rawData, 'serialNumber', __('N/A'))" />
            </div>
            
            <flux:button wire:click="check" wire:loading.attr="disabled">{{ __('Check Firmware Updates') }}</flux:button>

                <flux:button wire:click="cancel" wire:loading.attr="disabled" variant="danger">{{ __('Cancel Upgrade') }}</flux:button>

            @if($errorMessage !== '')
                <flux:callout variant="danger" icon="x-circle">
                    <flux:callout.text>{{ $errorMessage }}</flux:callout.text>
                </flux:callout>
            @endif
        </x-card>
        @if($availableVersions !== [])
            <x-card title="Availible Firmware" subtitle="Details about the firmware upgrade options">
                <flux:select label="Target Firmware Version" wire:model.live="selectedVersion">
                    <option value="">{{ __('Select a version') }}</option>
                    @foreach($availableVersions as $version)
                        <option value="{{ $version }}">{{ $version }}</option>
                    @endforeach
                </flux:select> 
                @if($selectedVersion !== '')
                    <div class="flex flex-col gap-2 mt-4">
                        <x-card-details-input type="datetime-local" label="Upgrade At" wire:model.live="upgradeAt" />
                        <flux:button wire:click="plan" wire:loading.attr="disabled">{{ __('Plan Upgrade') }}</flux:button>
                    </div>
                @endif



                @foreach($firmwareVersions as $version)
                    <flux:separator text="Version Details - {{ $version['version'] }}" />
                    <div class="flex gap-2">
                        <x-card-details-row label="Version" :value="$version['version']" />
                        <x-card-details-row label="Size" :value="$version['size']" />
                    </div>
                    @if(array_key_exists('news', $version))
                        <flux:heading>News</flux:heading>
                        <div class="grid grid-cols-1 gap-1">
                            @foreach($version['news'] as $news)
                                <flux:text>{{ $news }}</flux:text>
                            @endforeach
                        </div>
                    @endif
                    @if(array_key_exists('bugs', $version))
                        <flux:heading>Bugs</flux:heading>
                        <div class="grid grid-cols-1 gap-1">
                            @foreach($version['bugs'] as $bug)
                                <flux:text>{{ $bug }}</flux:text>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </x-card>
        @endif
    @else
        <x-card title="Firmware Upgrade Planned" subtitle="ISCPP already planned for this firewall. Please check the details below.">
            <div>
                <x-card-details-row label="Planned Upgrade At" :value="$firewall->plannedFirmwareUpgradeAt?->format('Y-m-d H:i') ?? __('N/A')" />
            </div>
            <flux:button wire:click="cancel" wire:loading.attr="disabled" variant="danger">{{ __('Cancel Upgrade') }}</flux:button>

            @if($errorMessage !== '')
                <flux:callout variant="danger" icon="x-circle">
                    <flux:callout.text>{{ $errorMessage }}</flux:callout.text>
                </flux:callout>
            @endif
        </x-card>
    @endif
    
</div>