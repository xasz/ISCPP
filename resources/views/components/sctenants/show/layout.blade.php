@props(['sctenant'])
<x-layouts.tabs>
    <x-slot name="tabs">
        <x-layouts.tabs.select :href="route('sctenants.tenantDetails', $sctenant)" label="Details" />
        <x-layouts.tabs.select :href="route('sctenants.tenantAlerts', $sctenant)" label="Alerts" />
        <x-layouts.tabs.select :href="route('sctenants.tenantBillables', $sctenant)" label="Billables" />
        <x-layouts.tabs.select :href="route('sctenants.tenantHealthscore', $sctenant)" label="Healthscore" />
        <x-layouts.tabs.select :href="route('sctenants.tenantEndpoints', $sctenant)" label="Endpoints" />
        <x-layouts.tabs.select :href="route('sctenants.tenantFirewalls', $sctenant)" label="Firewalls" />
        <x-layouts.tabs.select :href="route('sctenants.tenantISCPPSettings', $sctenant)" label="ISCPP Settings" />
        <x-layouts.tabs.select :href="route('sctenants.tenantRaw', $sctenant)" label="Raw" />
    </x-slot>
    {{ $slot }}
</x-layouts.tabs>