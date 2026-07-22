<x-layouts.app.sidebar>
    <flux:main class="space-y-4">
        <flux:navbar class="mb-4 border-b border-zinc-200 dark:border-zinc-700">
            {{ $tabs }}
        </flux:navbar>
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>
