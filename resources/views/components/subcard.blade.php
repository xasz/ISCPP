@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->class('my-4 rounded-lg border border-zinc-200 bg-zinc-50 p-4 text-zinc-800 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-zinc-200') }}>
    @if($title)
        <flux:heading size="sm" class="mb-1 text-zinc-900 dark:text-zinc-100">{{ $title }}</flux:heading>
    @endif
    @if($subtitle)
        <flux:text size="sm" class="mb-2 text-zinc-500 dark:text-zinc-400">{{ $subtitle }}</flux:text>
    @endif

    <div class="space-y-3">
        {{ $slot }}
    </div>
</div>
