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
    <x-header-card :title="$sctenant->name" icon="building-office-2" type="Tenant">
        <flux:badge size="sm">{{ ucfirst($sctenant->billingType) }}</flux:badge>
        <flux:badge color="zinc" size="sm">{{ $sctenant->dataGeography }}</flux:badge>
        <flux:badge color="violet" size="sm">{{ $sctenant->dataRegion }}</flux:badge>
    </x-header-card>
    {{ $slot }}
</x-layouts.tabs>