@props(['label', 'value'])
<flux:field class="mb-6">
    <flux:label>{{ $label }}</flux:label>
    <flux:input readonly value="{{ $value }}"/>
</flux:field>