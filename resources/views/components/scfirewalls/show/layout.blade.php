@props(['firewall'])
<x-layouts.tabs>
    <x-slot name="tabs">
        <x-layouts.tabs.select :href="route('scfirewalls.firewallDetails', ['id' => $firewall->id])" label="Details" />
        <x-layouts.tabs.select :href="route('scfirewalls.firewallFirmware', ['id' => $firewall->id])" label="Firmware" />
        <x-layouts.tabs.select :href="route('scfirewalls.firewallRaw', ['id' => $firewall->id])" label="Raw" />
    </x-slot>
    {{ $slot }}
</x-layouts.tabs>
