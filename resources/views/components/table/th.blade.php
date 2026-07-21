@props(['compact' => false])

<th scope="col" {{ $attributes->merge(['class' => $compact ? 'px-3 py-2' : 'px-4 py-3']) }}>
    {{$slot}}
</th>
