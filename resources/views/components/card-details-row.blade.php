@props(['label', 'value'])

<div {{ $attributes->class('flex flex-col gap-0.5 py-1.5') }}>
    <flux:text size="xs" class="font-medium tracking-wide text-zinc-400 uppercase dark:text-zinc-500">{{ $label }}</flux:text>
    <flux:text class="text-zinc-800 dark:text-zinc-200">{{ $value }}</flux:text>
</div>
