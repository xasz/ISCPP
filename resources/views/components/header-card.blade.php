@props(['icon', 'title', 'type' => ''])

<div {{ $attributes->class('overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900') }}>
    <div class="flex items-center gap-4 p-5">
        <div class="flex size-14 shrink-0 items-center justify-center rounded-lg bg-accent/10">
            <flux:icon name="{{ $icon }}" class="size-7 text-accent-content" />
        </div>
        <div class="flex min-w-0 flex-1 flex-col gap-1.5">
            @if($type)
                <flux:text size="xs" class="font-medium tracking-wide text-zinc-400 uppercase dark:text-zinc-500">{{ $type }}</flux:text>
            @endif
            <flux:heading size="xl" level="1" class="truncate text-zinc-900 dark:text-zinc-100">{{ $title }}</flux:heading>
            <div class="flex flex-wrap items-center gap-1.5">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
