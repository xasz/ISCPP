@props(['scalert'])
<x-layouts.tabs>
    <x-slot name="tabs">
        <x-layouts.tabs.select :href="route('scalerts.alertDetails', ['id' => $scalert->id])" label="Details" />
        <x-layouts.tabs.select :href="route('scalerts.alertRaw', ['id' => $scalert->id])" label="Raw" />
    </x-slot>
    <x-header-card :title="$scalert->description" icon="bell-alert" type="Alert">
        <x-scalerts.badge :scalert="$scalert" />
        <flux:badge color="zinc" size="sm">{{ $scalert->category }}</flux:badge>
        <flux:badge color="violet" size="sm">{{ $scalert->type }}</flux:badge>
    </x-header-card>
    {{ $slot }}
</x-layouts.tabs>