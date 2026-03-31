
@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 shadow-sm']) }}>
    @if($title || isset($headerActions))
    <div class="flex items-center justify-between px-5 py-4 border-b border-neutral-200 dark:border-neutral-700">
        <div>
            @if($title)
            <flux:heading size="lg" level="2" class="font-semibold">{{ $title }}</flux:heading>
            @endif
            @if($subtitle)
            <flux:text class="mt-0.5 text-sm text-neutral-500 dark:text-neutral-400">{{ $subtitle }}</flux:text>
            @endif
        </div>
        @if(isset($headerActions))
        <div class="flex items-center gap-2">
            {{ $headerActions }}
        </div>
        @endif
    </div>
    @endif
    <div class="p-5">
        {{ $slot }}
    </div>
</div>
