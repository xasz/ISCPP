@props(['score' => -1, 'size' => 'sm'])

@if($score < 0)
    <flux:badge :size="$size" class="w-10 justify-center">?</flux:badge>
@elseif($score <= 25)
    <flux:badge color="red" :size="$size" class="w-10 justify-center">{{ $score }}</flux:badge>
@elseif($score <= 50)
    <flux:badge color="orange" :size="$size" class="w-10 justify-center">{{ $score }}</flux:badge>
@elseif($score <= 75)
    <flux:badge color="amber" :size="$size" class="w-10 justify-center">{{ $score }}</flux:badge>
@else
    <flux:badge color="green" :size="$size" class="w-10 justify-center">{{ $score }}</flux:badge>
@endif