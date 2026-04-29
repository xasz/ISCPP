<x-scfirewalls.show.layout :firewall="$firewall">
    <x-header-card :title="$firewall->hostname" icon="fire" type="Firewall Firmware">
        <flux:badge size="sm">{{ data_get($firewall->rawData, 'serialNumber', __('N/A')) }}</flux:badge>
        <flux:badge color="zinc" size="sm">{{ data_get($firewall->rawData, 'firmwareVersion', __('N/A')) }}</flux:badge>
    </x-header-card>

    <livewire:scfirewalls.card-firmware-upgrade :firewall="$firewall" />
</x-scfirewalls.show.layout>