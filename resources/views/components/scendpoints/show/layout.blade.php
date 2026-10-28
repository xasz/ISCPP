@props(['endpoint'])
<x-layouts.tabs>
    <x-slot name="tabs">
        <x-layouts.tabs.select :href="route('scendpoints.endpointDetails', ['id' => $endpoint->id])" label="Details" />
        <x-layouts.tabs.select :href="route('scendpoints.endpointLive', ['id' => $endpoint->id])" label="Live" />
        <x-layouts.tabs.select :href="route('scendpoints.endpointRaw', ['id' => $endpoint->id])" label="Raw" />
    </x-slot>
    <x-header-card :title="$endpoint->hostname" icon="computer-desktop" type="Endpoint">
        <flux:badge size="sm">{{ ucfirst((string) $endpoint->type) }}</flux:badge>
        <flux:badge :color="App\Models\SCEndpoint::healthColor($endpoint->healthStatus)" size="sm">{{ __('Health') }}: {{ ucfirst((string) $endpoint->healthStatus) }}</flux:badge>
        <flux:badge :color="$endpoint->isOnline() ? 'green' : 'zinc'" size="sm">{{ $endpoint->isOnline() ? __('Online') : __('Offline') }}</flux:badge>
        <flux:badge :color="$endpoint->tamperProtectionEnabled ? 'green' : 'red'" size="sm">
            {{ $endpoint->tamperProtectionEnabled ? __('Tamper Enabled') : __('Tamper Disabled') }}
        </flux:badge>
        @if($endpoint->isIsolated())
            <flux:badge color="red" size="sm">{{ __('Isolated') }}</flux:badge>
        @endif
        @if($endpoint->SCTenant)
            <a href="{{ route('sctenants.tenantDetails', $endpoint->SCTenant) }}" wire:navigate>
                <flux:badge color="violet" size="sm" icon="building-office-2">{{ $endpoint->SCTenant->name }}</flux:badge>
            </a>
        @endif
        <x-slot:actions>
            <livewire:scendpoints.refresh-button :endpoint="$endpoint" />
        </x-slot:actions>
    </x-header-card>
    {{ $slot }}
</x-layouts.tabs>
