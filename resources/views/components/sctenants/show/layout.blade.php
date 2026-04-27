@props(['sctenant'])
<x-layouts.app>
    <flux:header class="mb-4">
        <flux:navbar>
            <flux:navbar.item :href="route('sctenants.tenantDetails', $sctenant)">Details</flux:navbar.item>
            <flux:navbar.item :href="route('sctenants.tenantAlerts', $sctenant)">Alerts</flux:navbar.item>
            <flux:navbar.item :href="route('sctenants.tenantBillables', $sctenant)">Billables</flux:navbar.item>
            <flux:navbar.item :href="route('sctenants.tenantHealthscore', $sctenant)">Healthscore</flux:navbar.item>
            <flux:navbar.item :href="route('sctenants.tenantEndpoints', $sctenant)">Endpoints</flux:navbar.item>
            <flux:navbar.item :href="route('sctenants.tenantFirewalls', $sctenant)">Firewalls</flux:navbar.item>
            <flux:navbar.item :href="route('sctenants.tenantISCPPSettings', $sctenant)">ISCPP Settings</flux:navbar.item>
        </flux:navbar>
    </flux:header>
    {{ $slot }}
</x-layouts.app>