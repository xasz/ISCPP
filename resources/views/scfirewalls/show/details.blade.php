<x-scfirewalls.show.layout :firewall="$firewall">
    @php
        $raw = $firewall->rawData ?? [];
        // The spec types cluster.peers as a list, its example shows a single object.
        $peers = data_get($raw, 'cluster.peers', []);
        $peers = is_array($peers) && array_is_list($peers) ? $peers : [$peers];
        $location = data_get($raw, 'geoLocation');
    @endphp

    <div class="grid gap-4 lg:grid-cols-2">
        <x-card title="{{ __('Firewall') }}">
            <div class="grid gap-x-6 sm:grid-cols-2">
                <x-card-details-row label="ID" :value="$firewall->id" />
                <x-card-details-row label="{{ __('Hostname') }}" :value="$firewall->hostname" />
                <x-card-details-row label="{{ __('Name') }}" :value="data_get($raw, 'name', __('N/A'))" />
                <x-card-details-row label="{{ __('Serial Number') }}" :value="data_get($raw, 'serialNumber', __('N/A'))" />
                <x-card-details-row label="{{ __('Model') }}" :value="data_get($raw, 'model', __('N/A'))" />
                <x-card-details-row label="{{ __('Firmware Version') }}" :value="data_get($raw, 'firmwareVersion', __('N/A'))" />
                <x-card-details-row label="{{ __('Group') }}" :value="data_get($raw, 'group.name', __('Ungrouped'))" />
                <x-card-details-row label="{{ __('Planned Firmware Upgrade') }}" :value="$firewall->plannedFirmwareUpgradeAt ? App\Services\ISCPPFormat::formatDateWithSeconds($firewall->plannedFirmwareUpgradeAt) : __('None')" />
            </div>
        </x-card>

        <x-card title="{{ __('Status') }}">
            <div class="grid gap-x-6 sm:grid-cols-2">
                <x-card-details-row label="{{ __('Connected') }}" :value="data_get($raw, 'status.connected') ? __('Yes') : __('No')" />
                <x-card-details-row label="{{ __('Suspended') }}" :value="data_get($raw, 'status.suspended') ? __('Yes') : __('No')" />
                <x-card-details-row label="{{ __('Managing Status') }}" :value="data_get($raw, 'status.managingStatus', __('N/A'))" />
                <x-card-details-row label="{{ __('Reporting Status') }}" :value="data_get($raw, 'status.reportingStatus', __('N/A'))" />
                <x-card-details-row label="{{ __('State Changed At') }}" :value="data_get($raw, 'stateChangedAt', __('N/A'))" />
                <x-card-details-row label="{{ __('Updated At') }}" :value="data_get($raw, 'updatedAt', __('N/A'))" />
            </div>
        </x-card>

        <x-card title="{{ __('Network') }}">
            <x-card-details-row label="{{ __('External IPv4 Addresses') }}" :value="collect(data_get($raw, 'externalIpv4Addresses', []))->implode(', ') ?: __('N/A')" />
            <x-card-details-row label="{{ __('Location') }}" :value="$location ? data_get($location, 'latitude').', '.data_get($location, 'longitude') : __('N/A')" />
        </x-card>

        <x-card title="{{ __('Capabilities & Cluster') }}">
            <div class="flex flex-wrap gap-1">
                @forelse(data_get($raw, 'capabilities', []) as $capability)
                    <flux:badge size="sm" color="zinc">{{ $capability }}</flux:badge>
                @empty
                    <flux:text size="sm" class="text-zinc-500">{{ __('No capabilities reported.') }}</flux:text>
                @endforelse
            </div>
            @if(data_get($raw, 'cluster'))
                <div class="grid gap-x-6 sm:grid-cols-2">
                    <x-card-details-row label="{{ __('Cluster Mode') }}" :value="data_get($raw, 'cluster.mode', __('N/A'))" />
                    <x-card-details-row label="{{ __('Cluster Status') }}" :value="data_get($raw, 'cluster.status', __('N/A'))" />
                    <x-card-details-row label="{{ __('Cluster Peers') }}" :value="collect($peers)->pluck('serialNumber')->filter()->implode(', ') ?: __('N/A')" />
                </div>
            @else
                <flux:text size="sm" class="text-zinc-500">{{ __('Not part of a cluster.') }}</flux:text>
            @endif
        </x-card>
    </div>
</x-scfirewalls.show.layout>
