<x-scfirewalls.show.layout :firewall="$firewall">
        <x-header-card :title="$firewall->hostname" icon="fire" type="Firewall">
            <flux:badge size="sm">{{ data_get($firewall->rawData, 'serialNumber', __('N/A')) }}</flux:badge>
            <flux:badge color="zinc" size="sm">{{ data_get($firewall->rawData, 'firmwareVersion', __('N/A')) }}</flux:badge>
            <flux:badge :color="data_get($firewall->rawData, 'status.connected') ? 'emerald' : 'red'" size="sm">
                {{ data_get($firewall->rawData, 'status.connected') ? __('Connected') : __('Offline') }}
            </flux:badge>
        </x-header-card>

        <x-card title="Firewall Details">
            <div>
                <x-card-details-row icon="identification" label="ID" :value="$firewall->id" />
                <x-card-details-row icon="fire" label="Hostname" :value="$firewall->hostname" />
                <x-card-details-row icon="building-office-2" label="Tenant" :value="$firewall->SCTenant?->name ?? __('Unknown')" />
                <x-card-details-row icon="tag" label="Serial Number" :value="data_get($firewall->rawData, 'serialNumber', __('N/A'))" />
                <x-card-details-row icon="server" label="Model" :value="data_get($firewall->rawData, 'model', __('N/A'))" />
                <x-card-details-row icon="command-line" label="Firmware Version" :value="data_get($firewall->rawData, 'firmwareVersion', __('N/A'))" />
                <x-card-details-row icon="signal" label="Connected" :value="data_get($firewall->rawData, 'status.connected') ? __('Yes') : __('No')" />
                <x-card-details-row icon="pause-circle" label="Suspended" :value="data_get($firewall->rawData, 'status.suspended') ? __('Yes') : __('No')" />
                <x-card-details-row icon="shield-check" label="Managing Status" :value="data_get($firewall->rawData, 'status.managingStatus', __('N/A'))" />
                <x-card-details-row icon="megaphone" label="Reporting Status" :value="data_get($firewall->rawData, 'status.reportingStatus', __('N/A'))" />
                <x-card-details-row icon="globe-alt" label="External IPv4 Addresses" :value="collect(data_get($firewall->rawData, 'externalIpv4Addresses', []))->implode(', ') ?: __('N/A')" />
                <x-card-details-row icon="clock" label="Updated At" :value="data_get($firewall->rawData, 'updatedAt', __('N/A'))" />
            </div>
        </x-card>
</x-scfirewalls.show.layout>
