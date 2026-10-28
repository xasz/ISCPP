@props(['firewall'])
@php
    $connected = (bool) data_get($firewall->rawData, 'status.connected');
@endphp
<x-layouts.tabs>
    <x-slot name="tabs">
        <x-layouts.tabs.select :href="route('scfirewalls.firewallDetails', ['id' => $firewall->id])" label="Details" />
        <x-layouts.tabs.select :href="route('scfirewalls.firewallFirmware', ['id' => $firewall->id])" label="Firmware" />
        <x-layouts.tabs.select :href="route('scfirewalls.firewallBackups', ['id' => $firewall->id])" label="Backups" />
        <x-layouts.tabs.select :href="route('scfirewalls.firewallRaw', ['id' => $firewall->id])" label="Raw" />
    </x-slot>
    <x-header-card :title="$firewall->hostname" icon="fire" type="Firewall">
        <flux:badge size="sm">{{ data_get($firewall->rawData, 'serialNumber', __('N/A')) }}</flux:badge>
        <flux:badge color="zinc" size="sm">{{ data_get($firewall->rawData, 'firmwareVersion', __('N/A')) }}</flux:badge>
        <flux:badge :color="$connected ? 'green' : 'red'" size="sm">{{ $connected ? __('Connected') : __('Disconnected') }}</flux:badge>
        @if(data_get($firewall->rawData, 'status.suspended'))
            <flux:badge color="amber" size="sm">{{ __('Suspended') }}</flux:badge>
        @endif
        @if($firewall->SCTenant)
            <a href="{{ route('sctenants.tenantDetails', $firewall->SCTenant) }}" wire:navigate>
                <flux:badge color="violet" size="sm" icon="building-office-2">{{ $firewall->SCTenant->name }}</flux:badge>
            </a>
        @endif
        <x-slot:actions>
            <livewire:scfirewalls.refresh-button :firewall="$firewall" />
        </x-slot:actions>
    </x-header-card>
    {{ $slot }}
</x-layouts.tabs>
