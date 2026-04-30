<x-layouts.app.sidebar>
    <flux:header class="mb-4">
        <flux:navbar>
            {{ $tabs }}
        </flux:navbar>
    </flux:header>
    <flux:main class="space-y-4">
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>
