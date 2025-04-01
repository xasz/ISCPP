<x-card {{ $attributes->merge(['class' => '']) }}>
    <flux:subheading>{{ $title }}</flux:subheading>
    <flux:heading size="xl" class="mb-2">{{ $value }}</flux:heading>
    <div class="absolute top-0 right-0 pr-2 pt-2">
        <flux:button icon="ellipsis-horizontal" variant="subtle" size="sm" />
    </div>
</x-card>

