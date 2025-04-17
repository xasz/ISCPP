@props(['json'])
@php
    $pretty = collect(json_decode($json, true))->toJson(JSON_PRETTY_PRINT);
@endphp    
<div class="mb-2 overflow-scroll">
    <label for="tenant" class="mb-1 ml-1 block text-xs font-normal text-gray-700 dark:text-gray-200">
        {{ __('Raw Data') }}:
    </label>
    <pre class="text-xs text-gray-700 bg-gray-50 dark:bg-gray-700 dark:text-gray-400 rounded-md p-1 pl-2">{{ $pretty }}</pre>
</div>      
