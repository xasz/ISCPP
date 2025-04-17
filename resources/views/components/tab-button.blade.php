@props(['name', 'label'])

<button
    @click="activeTab = '{{ $name }}'"
    :class="{ 'border-primary-500 text-primary-600': activeTab === '{{ $name }}', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== '{{ $name }}' }"
    class="py-2 px-1 font-medium text-sm border-b-2 focus:outline-none"
>
    {{ $label ?? $slot }}
</button>
