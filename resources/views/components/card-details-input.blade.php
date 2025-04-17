@props(['label', 'type' => 'text'])

<div class="mb-2 pt-4">
    <label for="tenant" class="mb-1 ml-1 block text-xs font-normal text-gray-700 dark:text-gray-200">
        {{ $label }}:
    </label>
    <input type="{{ $type }}" {{ $attributes }} class="mt-1 text-sm font-extrabol text-gray-700 bg-gray-50 dark:bg-gray-700 dark:text-gray-400 w-full rounded-md p-1 pl-2 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600"> 
</div>