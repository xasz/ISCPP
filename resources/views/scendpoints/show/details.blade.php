<x-scendpoints.show.layout :endpoint="$endpoint">
    <x-header-card :title="$endpoint->hostname" icon="computer-desktop" type="Endpoint">
        <flux:badge size="sm">{{ ucfirst($endpoint->type) }}</flux:badge>
        <flux:badge color="zinc" size="sm">{{ ucfirst((string) $endpoint->healthStatus) }}</flux:badge>
        <flux:badge :color="$endpoint->tamperProtectionEnabled ? 'emerald' : 'red'" size="sm">
            {{ $endpoint->tamperProtectionEnabled ? __('Tamper Enabled') : __('Tamper Disabled') }}
        </flux:badge>
    </x-header-card>

    <x-card title="Endpoint Details">
        <div>
            <x-card-details-row icon="identification" label="ID" :value="$endpoint->id" />
            <x-card-details-row icon="computer-desktop" label="Hostname" :value="$endpoint->hostname" />
            <x-card-details-row icon="building-office-2" label="Tenant" :value="$endpoint->SCTenant?->name ?? __('Unknown')" />
            <x-card-details-row icon="tag" label="Type" :value="$endpoint->type" />
            <x-card-details-row icon="heart" label="Health Status" :value="$endpoint->healthStatus" />
            <x-card-details-row icon="shield-check" label="Tamper Protection" :value="$endpoint->tamperProtectionEnabled ? __('Enabled') : __('Disabled')" />
            <x-card-details-row icon="clock" label="Last Seen" :value="$endpoint->lastSeen" />
            <x-card-details-row icon="signal" label="Online" :value="data_get($endpoint->rawData, 'online') ? __('Yes') : __('No')" />
            <x-card-details-row icon="user" label="Associated Person" :value="data_get($endpoint->rawData, 'associatedPerson.name', __('Unknown'))" />
            <x-card-details-row icon="command-line" label="Operating System" :value="data_get($endpoint->rawData, 'os.name', __('Unknown'))" />
            <x-card-details-row icon="globe-alt" label="IPv4 Addresses" :value="collect(data_get($endpoint->rawData, 'ipv4Addresses', []))->implode(', ') ?: __('N/A')" />
            <x-card-details-row icon="cpu-chip" label="Assigned Products" :value="collect(data_get($endpoint->rawData, 'assignedProducts', []))->pluck('code')->implode(', ') ?: __('N/A')" />
        </div>
    </x-card>
</x-scendpoints.show.layout>
