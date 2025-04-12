@props(['title' => null, 'subtitle' => null])
<div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <x-card title="{{ $title }}" subtitle="{{ $subtitle }}">
        <div class="p-4">
            {{ $slot }}
        </div>
        <x-a-button wire:click="closeModal()">
            {{ __('Close') }}
        </x-a-button>
    </x-card>
</div>