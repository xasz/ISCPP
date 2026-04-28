@props(['icon', 'title', 'type' => ''])

<x-card :title="$type">
    <div class="flex items-end gap-4">
        <div>
            <flux:icon name="{{ $icon }}" class="h-16 w-16 text-blue-600 dark:text-blue-400" />
        </div>
        <div class="flex flex-col gap-2">
            <flux:heading size="xl" level="1" class="truncate">{{ $title }}</flux:heading>
            <div>
                {{ $slot }}
            </div>
        </div>
    </div>
</x-card>