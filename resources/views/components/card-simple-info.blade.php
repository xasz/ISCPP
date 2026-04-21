<x-card {{ $attributes->merge(['class' => '']) }}>
    <div class="flex w-full">
        <flux:heading size="xl" class="mb-2 flex-1">{{ $value }}</flux:heading>
         @if(isset($menu))
            <flux:dropdown>
                <flux:button icon:trailing="ellipsis-horizontal"></flux:button>
                <flux:menu>
                    {{ $menu }}
                </flux:menu>
            </flux:dropdown>
        @endif
    </div>
    @endif
</div>
