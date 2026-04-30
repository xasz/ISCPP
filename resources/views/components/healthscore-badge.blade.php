@props(['score' => -1, 'size' => 'sm'])

@if($score <= 0)
    <flux:badge :size="$size">?</flux:badge>
@elseif($score <= 25)
    <flux:badge color="red" :size="$size">{{ $score }}</flux:badge>
@elseif($score <= 50)
    <flux:badge color="orange" :size="$size">{{ $score }}</flux:badge>
@elseif($score <= 75)
    <flux:badge color="amber" :size="$size">{{ $score }}</flux:badge>
@else
    <flux:badge color="green" :size="$size">{{ $score }}</flux:badge>
@endif