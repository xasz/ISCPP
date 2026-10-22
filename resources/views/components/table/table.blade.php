@props(['nofull' => false])
<table {{ $attributes->merge(['class' => ($nofull ? '' : 'w-full') . ' text-sm text-left rtl:text-right table-fixed']) }}>
    {{$slot}}
</table>
