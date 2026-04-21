@props(['name', 'label'])

<button
    @click="activeTab = '{{ $name }}'"
    :class="{
        'border-b-2 border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400 font-semibold': activeTab === '{{ $name }}',
        'border-b-2 border-transparent text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 hover:border-neutral-300 dark:hover:border-neutral-600': activeTab !== '{{ $name }}'
    }"
    class="whitespace-nowrap px-4 py-3 text-sm focus:outline-none transition-colors"
>
    {{ $label ?? $slot }}
</button>
