@props(['node', 'selected' => '', 'depth' => 0])

@php
    $isBranch = $node['children'] !== [];
    $isSelected = $node['key'] !== null && $node['key'] === $selected;
    // Keep a branch open when the selected event lives somewhere below it.
    $containsSelected = $selected !== '' && str_starts_with($selected, $node['path'].'.');
    $dotClass = match ($node['severity']?->badgeColor()) {
        'amber' => 'bg-amber-500',
        'red' => 'bg-red-500',
        'blue' => 'bg-blue-500',
        default => 'bg-zinc-400',
    };
@endphp

<li>
    @if($isBranch)
        <div x-data="{ open: {{ $depth === 0 || $containsSelected ? 'true' : 'false' }} }">
            <button type="button" x-on:click="open = ! open"
                    class="flex w-full items-center gap-1 rounded-md px-2 py-1 text-left text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800">
                <flux:icon.chevron-right variant="micro" class="size-4 shrink-0 transition-transform" x-bind:class="open && 'rotate-90'" />
                <span>{{ $node['label'] }}</span>
            </button>

            <ul x-show="open" class="ml-3 border-l border-zinc-200 pl-2 dark:border-zinc-700">
                @if($node['key'] !== null)
                    {{-- A key that is also a prefix of other keys gets its own entry. --}}
                    <x-iscpp.event-tree-node :node="array_merge($node, ['children' => []])" :selected="$selected" :depth="$depth + 1" />
                @endif
                @foreach($node['children'] as $child)
                    <x-iscpp.event-tree-node :node="$child" :selected="$selected" :depth="$depth + 1" />
                @endforeach
            </ul>
        </div>
    @else
        <button type="button" wire:click="select('{{ $node['key'] }}')"
                @class([
                    'flex w-full items-center gap-2 rounded-md px-2 py-1 text-left text-sm',
                    'bg-zinc-800 text-white dark:bg-zinc-100 dark:text-zinc-900' => $isSelected,
                    'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' => ! $isSelected,
                ])>
            <span class="inline-block size-2 shrink-0 rounded-full {{ $dotClass }}"></span>
            <span>{{ $node['label'] }}</span>
        </button>
    @endif
</li>
