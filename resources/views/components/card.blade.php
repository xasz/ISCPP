@props([
    'title' => null,
    'subtitle' => null,
    'description' => null,
])

@php
    $cardSubtitle = $subtitle ?? $description;
@endphp

<div {{ $attributes->class('overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm transition-shadow dark:border-zinc-800 dark:bg-zinc-900') }}>
    @if($title || $cardSubtitle)
        <header class="flex flex-col gap-0.5 border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
            @if($title)
                <flux:heading size="base" level="2" class="text-zinc-900 dark:text-zinc-100">{{ $title }}</flux:heading>
            @endif

            @if($cardSubtitle)
                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $cardSubtitle }}</flux:text>
            @endif
        </header>
    @endif

    <div class="space-y-4 p-5 text-zinc-800 dark:text-zinc-200">
        {{ $slot }}
    </div>
</div>
