@props(['title' => null, 'subtitle' => null, 'name' => null, 'maxWidth' => 'md'])

@php
    $maxWidthClass = match ($maxWidth) {
        'sm' => 'sm:max-w-sm',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        default => 'sm:max-w-md',
    };
@endphp

<div
    x-data
    x-on:keydown.escape.window="$wire.closeModal()"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
>
    <div
        x-show="true"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        wire:click="closeModal"
        class="fixed inset-0 bg-zinc-950/60 backdrop-blur-sm"
    ></div>

    <div
        x-show="true"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        {{ $attributes->class(["relative w-full {$maxWidthClass} overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900"]) }}
    >
        @if($title || $subtitle)
            <header class="flex items-start justify-between gap-4 border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                <div class="flex flex-col gap-0.5">
                    @if($title)
                        <flux:heading size="base" level="2" class="text-zinc-900 dark:text-zinc-100">{{ $title }}</flux:heading>
                    @endif
                    @if($subtitle)
                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $subtitle }}</flux:text>
                    @endif
                </div>
                <flux:button wire:click="closeModal" variant="ghost" size="sm" icon="x-mark" inset="top bottom" />
            </header>
        @endif

        <div class="max-h-[70vh] space-y-4 overflow-y-auto p-5 text-zinc-800 dark:text-zinc-200">
            {{ $slot }}
        </div>
    </div>
</div>
