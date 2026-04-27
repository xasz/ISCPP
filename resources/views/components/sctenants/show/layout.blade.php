@props(['sctenant'])
<x-layouts.tabs>
    <x-slot name="tabs">
        <x-layouts.tabs.select :href="route('sctenants.tenantDetails', $sctenant)" />
        <x-layouts.tabs.select :href="route('sctenants.tenantAlerts', $sctenant)" />
        <x-layouts.tabs.select :href="route('sctenants.tenantBillables', $sctenant)" />
        <x-layouts.tabs.select :href="route('sctenants.tenantHealthscore', $sctenant)" />
        <x-layouts.tabs.select :href="route('sctenants.tenantEndpoints', $sctenant)" />
        <x-layouts.tabs.select :href="route('sctenants.tenantFirewalls', $sctenant)" />
        <x-layouts.tabs.select :href="route('sctenants.tenantISCPPSettings', $sctenant)" />
    </x-slot>
    {{ $slot }}
</x-layouts.tabs>