<div {{ $attributes->merge(['class' => 'relative rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 shadow-sm p-5']) }}>
    <flux:subheading class="text-xs font-medium uppercase tracking-wide text-neutral-400 dark:text-neutral-500">{{ $title }}</flux:subheading>
    <flux:heading size="xl" class="mt-1 text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ $value }}</flux:heading>
    @if(isset($menu))
    <div class="absolute top-3 right-3">
        <flux:dropdown>
            <flux:button icon:trailing="ellipsis-horizontal" size="sm" variant="ghost"></flux:button>
            <flux:menu>
                {{ $menu }}
            </flux:menu>
        </flux:dropdown>
    </div>
    @endif
</div>
