<x-layouts.app>
    <div class="mb-4">
        <flux:heading size="xl">User Management Settings</flux:heading>
        <flux:text class="text-gray-600 dark:text-gray-400">Manage, invite and remove users</flux:text>
    </div>
    <x-tab-container defaultTab="manager">
        <x-slot name="tabs">
            <x-tab-button name="manager" label="Manager" />
            <x-tab-button name="invitation" label="Invites" />
        </x-slot>
        <x-slot name="content">            
            <x-tab-panel name="manager">

            </x-tab-panel>     
            <x-tab-panel name="invitation">

            </x-tab-panel>
        </x-slot>
    </x-tab-container>
</x-layouts.app>
