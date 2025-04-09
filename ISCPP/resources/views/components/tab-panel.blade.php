@props(['name'])

<div x-show="activeTab === '{{ $name }}'" {{ $attributes }}>
    {{ $slot }}
</div>
