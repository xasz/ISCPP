@props([
    'title' => null,
    'subtitle' => null,
    'description' => null,
])

@php
    $cardSubtitle = $subtitle ?? $description;
@endphp

<div {{ $attributes->class('overflow-hidden rounded-xl border border-zinc-200 shadow-md ring-1 ring-zinc-950 transition-shadow hover:shadow-lg dark:border-zinc-700 dark:shadow-black dark:ring-white') }}>
    @if($title || $cardSubtitle)
        <header class="p-4 border-b border-zinc-200 px-5 py-3 dark:border-zinc-700  bg-zinc-50 dark:bg-zinc-900">
            @if($title)
                <flux:heading size="base" level="2" class="text-zinc-900 dark:text-zinc-100">{{ $title }}</flux:heading>
            @endif

            @if($cardSubtitle)
                <flux:text size="sm" class="mt-1 block text-zinc-600 dark:text-zinc-400">{{ $cardSubtitle }}</flux:text>
            @endif
        </header>
    @endif

    <div class="space-y-4 p-4 text-zinc-800 dark:text-zinc-200">
        {{ $slot }}
    </div>
</div>
