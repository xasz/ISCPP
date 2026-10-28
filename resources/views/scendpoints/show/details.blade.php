<x-scendpoints.show.layout :endpoint="$endpoint">
    @php
        $raw = $endpoint->rawData ?? [];
        $os = data_get($raw, 'os', []);
        $osVersion = collect([data_get($os, 'majorVersion'), data_get($os, 'minorVersion'), data_get($os, 'build')])
            ->filter(fn ($part) => $part !== null)
            ->implode('.');
        $services = data_get($raw, 'health.services.serviceDetails', []);
        $products = data_get($raw, 'assignedProducts', []);
    @endphp

    <div class="grid gap-4 lg:grid-cols-2">
        <x-card title="{{ __('Endpoint') }}">
            <div class="grid gap-x-6 sm:grid-cols-2">
                <x-card-details-row label="ID" :value="$endpoint->id" />
                <x-card-details-row label="{{ __('Hostname') }}" :value="$endpoint->hostname" />
                <x-card-details-row label="{{ __('Type') }}" :value="ucfirst((string) $endpoint->type)" />
                <x-card-details-row label="{{ __('Group') }}" :value="data_get($raw, 'group.name', __('N/A'))" />
                <x-card-details-row label="{{ __('Associated Person') }}" :value="data_get($raw, 'associatedPerson.name', __('Unknown'))" />
                <x-card-details-row label="{{ __('Login') }}" :value="data_get($raw, 'associatedPerson.viaLogin', __('N/A'))" />
                <x-card-details-row label="{{ __('Last Seen') }}" :value="$endpoint->lastSeen ? App\Services\ISCPPFormat::formatDateWithSeconds($endpoint->lastSeen) : __('Never')" />
                <x-card-details-row label="{{ __('Online') }}" :value="$endpoint->isOnline() ? __('Yes') : __('No')" />
            </div>
        </x-card>

        <x-card title="{{ __('Operating System') }}">
            <div class="grid gap-x-6 sm:grid-cols-2">
                <x-card-details-row label="{{ __('Name') }}" :value="trim((string) data_get($os, 'name', __('Unknown')))" />
                <x-card-details-row label="{{ __('Platform') }}" :value="data_get($os, 'platform', __('Unknown'))" />
                <x-card-details-row label="{{ __('Version') }}" :value="$osVersion ?: __('N/A')" />
                <x-card-details-row label="{{ __('Server') }}" :value="data_get($os, 'isServer') ? __('Yes') : __('No')" />
            </div>
        </x-card>

        <x-card title="{{ __('Health') }}">
            <div class="flex flex-wrap gap-2">
                <flux:badge :color="App\Models\SCEndpoint::healthColor(data_get($raw, 'health.overall'))" size="sm">{{ __('Overall') }}: {{ data_get($raw, 'health.overall', __('unknown')) }}</flux:badge>
                <flux:badge :color="App\Models\SCEndpoint::healthColor(data_get($raw, 'health.threats.status'))" size="sm">{{ __('Threats') }}: {{ data_get($raw, 'health.threats.status', __('unknown')) }}</flux:badge>
                <flux:badge :color="App\Models\SCEndpoint::healthColor(data_get($raw, 'health.services.status'))" size="sm">{{ __('Services') }}: {{ data_get($raw, 'health.services.status', __('unknown')) }}</flux:badge>
            </div>
            @if(count($services) > 0)
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach($services as $service)
                        <div class="flex items-center justify-between gap-4 py-1.5">
                            <flux:text size="sm" class="truncate">{{ data_get($service, 'name') }}</flux:text>
                            <flux:badge :color="data_get($service, 'status') === 'running' ? 'green' : 'red'" size="sm">{{ data_get($service, 'status') }}</flux:badge>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        <x-card title="{{ __('Protection') }}">
            <div class="grid gap-x-6 sm:grid-cols-2">
                <x-card-details-row label="{{ __('Tamper Protection') }}" :value="$endpoint->tamperProtectionEnabled ? __('Enabled') : __('Disabled')" />
                <x-card-details-row label="{{ __('Tamper Protection Supported') }}" :value="data_get($raw, 'tamperProtectionSupported') ? __('Yes') : __('No')" />
                <x-card-details-row label="{{ __('Isolation') }}" :value="data_get($raw, 'isolation.status', __('N/A'))" />
                <x-card-details-row label="{{ __('Lockdown') }}" :value="data_get($raw, 'lockdown.status', __('N/A'))" />
                <x-card-details-row label="{{ __('Encryption') }}" :value="data_get($raw, 'encryption.overallStatus', __('N/A'))" />
                <x-card-details-row label="{{ __('MDR Managed') }}" :value="data_get($raw, 'mdrManaged') ? __('Yes') : __('No')" />
            </div>
        </x-card>

        <x-card title="{{ __('Network') }}">
            <x-card-details-row label="{{ __('IPv4 Addresses') }}" :value="collect(data_get($raw, 'ipv4Addresses', []))->implode(', ') ?: __('N/A')" />
            <x-card-details-row label="{{ __('IPv6 Addresses') }}" :value="collect(data_get($raw, 'ipv6Addresses', []))->implode(', ') ?: __('N/A')" />
            <x-card-details-row label="{{ __('MAC Addresses') }}" :value="collect(data_get($raw, 'macAddresses', []))->implode(', ') ?: __('N/A')" />
        </x-card>

        <x-card title="{{ __('Assigned Products') }}">
            @forelse($products as $product)
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <flux:text class="font-medium text-zinc-800 dark:text-zinc-200">{{ data_get($product, 'code') }}</flux:text>
                        <flux:text size="sm">{{ data_get($product, 'version') }}</flux:text>
                    </div>
                    <flux:badge :color="data_get($product, 'status') === 'installed' ? 'green' : 'zinc'" size="sm">{{ data_get($product, 'status') }}</flux:badge>
                </div>
            @empty
                <flux:text size="sm" class="text-zinc-500">{{ __('No products assigned.') }}</flux:text>
            @endforelse
        </x-card>
    </div>
</x-scendpoints.show.layout>
