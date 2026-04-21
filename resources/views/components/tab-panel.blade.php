@props(['name'])

<div x-show="activeTab === '{{ $name }}'" x-cloak {{ $attributes }}>
    {{ $slot }}
</div>
