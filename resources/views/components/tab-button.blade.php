@props(['name', 'label'])

<button
    @click="activeTab = '{{ $name }}'"
    :class="{
        'border-b-2 border-accent text-accent-content font-semibold': activeTab === '{{ $name }}',
        'border-b-2 border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:border-zinc-300 dark:hover:border-zinc-600': activeTab !== '{{ $name }}'
    }"
    class="whitespace-nowrap px-4 py-3 text-sm focus:outline-none transition-colors"
>
    {{ $label ?? $slot }}
</button>
