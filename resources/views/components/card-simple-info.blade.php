@props(['title', 'value'])

<div {{ $attributes->class('overflow-hidden rounded-xl border border-zinc-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900') }}>
    <div class="flex w-full items-start justify-between gap-2">
        <div class="flex flex-col gap-1.5">
            <flux:text size="sm" class="font-medium text-zinc-500 dark:text-zinc-400">{{ $title }}</flux:text>
            <flux:heading size="xl" class="text-3xl font-semibold tabular-nums text-zinc-900 dark:text-zinc-100">{{ $value }}</flux:heading>
        </div>
        @if(isset($menu))
            <flux:dropdown>
                <flux:button icon="ellipsis-horizontal" variant="ghost" size="sm"></flux:button>
                <flux:menu>
                    {{ $menu }}
                </flux:menu>
            </flux:dropdown>
        @endif
    </div>
</div>
