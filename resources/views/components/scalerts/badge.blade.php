@props(['scalert'])
@php
    $color = match(strtolower($scalert->severity ?? '')) {
        'high'   => 'red',
        'medium' => 'amber',
        'low'    => 'zinc',
        default  => 'zinc',
    };
@endphp
<flux:badge color="{{ $color }}" size="sm">
    {{ $scalert->severity }}
</flux:badge>