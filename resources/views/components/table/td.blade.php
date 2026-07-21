@props(['compact' => false])

<td {{ $attributes->merge(['class' => ($compact ? 'px-3 py-1.5 text-xs' : 'px-4 py-3').' overflow-hidden text-ellipsis whitespace-nowrap']) }}>
    {{$slot}}
</td>
