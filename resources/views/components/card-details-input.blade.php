@props(['label', 'type' => 'text', 'errorModel' => null])

<div class="mt-4">
    <flux:input :label="$label" :type="$type" {{ $attributes }} />
</div>
