@props([
    'title' => null,
    'subtitle' => null,
    'description' => null,
])

@php
    $cardSubtitle = $subtitle ?? $description;
@endphp

<div {{ $attributes->class('p-5 overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm transition-shadow dark:border-zinc-800 dark:bg-zinc-900') }}>
    @if($title || $cardSubtitle)
        <header class="flex flex-col gap-0.5 border-b pb-3 border-zinc-100 dark:border-zinc-800 mb-4">
            @if($title)
                <flux:heading>{{ $title }}</flux:heading>
            @endif

            @if($cardSubtitle)
                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $cardSubtitle }}</flux:text>
            @endif
        </header>
    @endif

    <div class="space-y-4 text-zinc-800 dark:text-zinc-200">
        {{ $slot }}
    </div>
</div>
