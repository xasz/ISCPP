<x-layouts.app>
    <div class="mb-4">
        <flux:heading size="xl">System Settings</flux:heading>
        <flux:text class="text-gray-600 dark:text-gray-400">Configure your system integration settings</flux:text>
    </div>
    <livewire:settings.card-sc />
    <x-tab-container defaultTab="webhook">
        <x-slot name="tabs">
            <x-tab-button name="webhook" label="Webhook Alerts" />
            <x-tab-button name="halo" label="Halo Integration" />
            <x-tab-button name="ninja" label="NinjaOne Integration" />
            <x-tab-button name="commands" label="Admin Commands" />
        </x-slot>
        <x-slot name="content">            
            <x-tab-panel name="webhook">
                <livewire:settings.card-webhook-alert />
            </x-tab-panel>
            <x-tab-panel name="halo">
                <livewire:settings.card-haloServiceSettings />
            </x-tab-panel>
            <x-tab-panel name="ninja">
                Comming soon
            </x-tab-panel>
            <x-tab-panel name="commands">
                <livewire:settings.card-commands />
            </x-tab-panel>
        </x-slot>
    </x-tab-container>
</x-layouts.app>
