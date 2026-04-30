@props(['endpoint'])
<x-layouts.tabs>
    <x-slot name="tabs">
        <x-layouts.tabs.select :href="route('scendpoints.endpointDetails', ['id' => $endpoint->id])" label="Details" />
        <x-layouts.tabs.select :href="route('scendpoints.endpointRaw', ['id' => $endpoint->id])" label="Raw" />
    </x-slot>
    {{ $slot }}
</x-layouts.tabs>
