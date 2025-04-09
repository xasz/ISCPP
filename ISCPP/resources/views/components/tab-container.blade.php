@props(['defaultTab' => 'first'])

<div x-data="{ activeTab: '{{ $defaultTab }}' }" {{ $attributes->class(['mb-6']) }}>
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-6">
            {{ $tabs }}
        </nav>
    </div>

    <div class="mt-6">
        {{ $content }}
    </div>
</div>
