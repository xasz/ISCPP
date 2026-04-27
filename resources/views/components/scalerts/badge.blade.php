@props(['scalert'])
@php
    $color = match(strtolower($scalert->severity ?? '')) {
        'high'   => 'red',
        'medium' => 'yellow',
        'low'    => 'blue',
        default  => 'neutral',
    };
@endphp
<flux:badge color="{{ $color }}" size="sm">
    {{ $scalert->severity }}
</flux:badge>