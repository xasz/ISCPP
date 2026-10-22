@props(['score' => -1, 'size' => 'sm', 'threshold' => null])

@php
    // A score under its threshold is what raises ISCPPHealthScoreThresholdDropped, so mark it here too.
    $below = $threshold !== null && $score >= 0 && $score < (int) $threshold;
    $ringClass = $below ? ' ring-1 ring-red-400' : '';
    $title = $below ? __('Below threshold of :threshold', ['threshold' => $threshold]) : null;
@endphp

@if($score < 0)
    <flux:badge :size="$size" class="w-10 justify-center">?</flux:badge>
@elseif($score <= 25)
    <flux:badge color="red" :size="$size" :title="$title" class="w-10 justify-center{{ $ringClass }}">{{ $score }}</flux:badge>
@elseif($score <= 50)
    <flux:badge color="orange" :size="$size" :title="$title" class="w-10 justify-center{{ $ringClass }}">{{ $score }}</flux:badge>
@elseif($score <= 75)
    <flux:badge color="amber" :size="$size" :title="$title" class="w-10 justify-center{{ $ringClass }}">{{ $score }}</flux:badge>
@else
    <flux:badge color="green" :size="$size" :title="$title" class="w-10 justify-center{{ $ringClass }}">{{ $score }}</flux:badge>
@endif