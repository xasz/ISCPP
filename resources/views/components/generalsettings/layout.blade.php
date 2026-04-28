<x-layouts.tabs>
    <x-slot name="tabs">
        <x-layouts.tabs.select :href="route('generalsettings.sc')" label="Sophos Central" />
        <x-layouts.tabs.select :href="route('generalsettings.webhookAlerts')" label="Webhook Alerts" />
        <x-layouts.tabs.select :href="route('generalsettings.halo')" label="Halo Integration" />
        <x-layouts.tabs.select :href="route('generalsettings.ninja')" label="NinjaOne Integration" />
        <x-layouts.tabs.select :href="route('generalsettings.commands')" label="Admin Commands" />
    </x-slot>
    {{ $slot }}
</x-layouts.tabs>
