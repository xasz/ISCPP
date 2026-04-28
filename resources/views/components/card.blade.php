@props([
    'title' => null,
    'subtitle' => null,
    'description' => null,
])

@php
    $cardSubtitle = $subtitle ?? $description;
@endphp

<section {{ $attributes->class('overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50/60 shadow-md ring-1 ring-zinc-950/5 transition-shadow hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-900/70 dark:shadow-black/30 dark:ring-white/10') }}>
    @if($title || $cardSubtitle)
        <header class="border-b border-zinc-200/80 bg-zinc-100/70 px-5 py-3 dark:border-zinc-700 dark:bg-zinc-900">
            @if($title)
                <flux:heading size="base" level="2" class="text-zinc-900 dark:text-zinc-100">{{ $title }}</flux:heading>
            @endif

            @if($cardSubtitle)
                <flux:text size="sm" class="mt-1 block text-zinc-600 dark:text-zinc-400">{{ $cardSubtitle }}</flux:text>
            @endif
        </header>
    @endif

    <div class="space-y-4 px-5 py-4 text-zinc-800 dark:text-zinc-200">
        {{ $slot }}
    </div>
</section>
