@props(['scalert'])
<x-layouts.tabs>
    <x-slot name="tabs">
        <x-layouts.tabs.select :href="route('scalerts.alertDetails', ['id' => $scalert->id])" label="Details" />
        <x-layouts.tabs.select :href="route('scalerts.alertRaw', ['id' => $scalert->id])" label="Raw" />
    </x-slot>
    {{ $slot }}
</x-layouts.tabs>