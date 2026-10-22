<?php

use App\Events\ISCPPEventSeverity;
use App\Services\ISCPPEventRegistry;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new class extends Component {
    /**
     * Selected event key. Empty shows the overview. Kept in the URL so a link can point
     * straight at one event.
     */
    #[Url(as: 'event')]
    public string $selected = '';

    public function select(string $key): void
    {
        $this->selected = in_array($key, app(ISCPPEventRegistry::class)->keys(), true) ? $key : '';
    }

    public function with(ISCPPEventRegistry $registry): array
    {
        return [
            'tree' => $registry->tree(),
            // An unknown key from an old link falls back to the overview.
            'event' => $this->selected !== '' ? $registry->describe($this->selected) : null,
            'severities' => ISCPPEventSeverity::cases(),
            'eventCount' => count($registry->keys()),
        ];
    }
}; ?>

<div class="grid grid-cols-1 gap-4 md:grid-cols-[18rem_1fr]">
    <x-card title="{{ __('Events') }}" subtitle="{{ $eventCount }} {{ __('event types') }}" class="self-start md:sticky md:top-4">
        <ul class="flex flex-col gap-0.5">
            <li>
                <button type="button" wire:click="select('')"
                        @class([
                            'flex w-full items-center gap-2 rounded-md px-2 py-1 text-left text-sm font-medium',
                            'bg-zinc-800 text-white dark:bg-zinc-100 dark:text-zinc-900' => $event === null,
                            'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800' => $event !== null,
                        ])>
                    <flux:icon.information-circle variant="micro" class="size-4 shrink-0" />
                    <span>{{ __('Overview') }}</span>
                </button>
            </li>

            @foreach($tree as $node)
                <x-iscpp.event-tree-node :node="$node" :selected="$event['key'] ?? ''" />
            @endforeach
        </ul>
    </x-card>

    <div class="min-w-0">
        @if($event === null)
            <x-card title="{{ __('ISCPP Event Info') }}" subtitle="{{ __('Every event the ISCPP event system can raise, generated from the event classes. Pick an event on the left.') }}">
                <flux:heading size="sm">{{ __('Severities') }}</flux:heading>
                <div class="flex flex-wrap gap-2">
                    @foreach($severities as $severity)
                        <flux:badge color="{{ $severity->badgeColor() }}" size="sm">{{ $severity->value }}</flux:badge>
                    @endforeach
                </div>

                <x-card-hr />

                <flux:heading size="sm">{{ __('Payload envelope') }}</flux:heading>
                <flux:text size="sm" class="text-zinc-500">
                    {{ __('Every webhook receives the same envelope. Only the data block differs per event.') }}
                </flux:text>

                <div class="relative overflow-x-auto">
                    <x-table.table>
                        <x-table.thead>
                            <tr>
                                <x-table.th compact class="w-1/4">{{ __('Field') }}</x-table.th>
                                <x-table.th compact>{{ __('Meaning') }}</x-table.th>
                            </tr>
                        </x-table.thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach([
                                'iscpp.payloadVersion' => __('Payload format version. Fields are only ever added within a version.'),
                                'iscpp.instance' => __('URL of the ISCPP instance that sent the event.'),
                                'event.id' => __('Unique event id. Also sent as Idempotency-Key header: deduplicate on it.'),
                                'event.key' => __('Machine key of the event, as shown in the tree.'),
                                'event.label' => __('Human readable event name.'),
                                'event.severity' => __('Severity of this occurrence.'),
                                'event.occurredAt' => __('ISO 8601 timestamp of when the event happened.'),
                                'tenant' => __('id, name, showAs, haloclientId, ninjaorgId. null for events without a tenant.'),
                                'subject' => __('type and id of the record the event is about. null when there is none.'),
                                'data' => __('Event specific properties.'),
                            ] as $field => $meaning)
                                <x-table.tr>
                                    <x-table.td compact class="font-mono text-xs">{{ $field }}</x-table.td>
                                    <x-table.td compact class="whitespace-normal">{{ $meaning }}</x-table.td>
                                </x-table.tr>
                            @endforeach
                        </tbody>
                    </x-table.table>
                </div>
            </x-card>
        @else
            <x-card :title="$event['label']" :subtitle="$event['key']" wire:key="event-{{ $event['key'] }}">
                <div class="flex flex-wrap items-center gap-2">
                    <flux:badge color="{{ $event['severity']->badgeColor() }}" size="sm">{{ $event['severity']->value }}</flux:badge>
                    @if($event['severityVaries'])
                        <flux:badge color="zinc" size="sm">{{ __('severity varies per event') }}</flux:badge>
                    @endif
                    <flux:text size="sm" class="font-mono text-zinc-400 dark:text-zinc-500">{{ $event['class'] }}</flux:text>
                </div>

                @if($event['description'] !== '')
                    <flux:text>{{ $event['description'] }}</flux:text>
                @endif

                <div class="relative overflow-x-auto">
                    <x-table.table>
                        <x-table.thead>
                            <tr>
                                <x-table.th compact class="w-1/4">{{ __('Property') }}</x-table.th>
                                <x-table.th compact class="w-24">{{ __('Type') }}</x-table.th>
                                <x-table.th compact>{{ __('Example') }}</x-table.th>
                                <x-table.th compact class="w-32">{{ __('Filterable') }}</x-table.th>
                            </tr>
                        </x-table.thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse($event['properties'] as $property)
                                <x-table.tr>
                                    <x-table.td compact class="font-mono text-xs">data.{{ $property['name'] }}</x-table.td>
                                    <x-table.td compact class="text-xs">{{ $property['type'] }}</x-table.td>
                                    <x-table.td compact class="whitespace-normal break-all font-mono text-xs">{{ $property['example'] }}</x-table.td>
                                    <x-table.td compact>
                                        @if(array_key_exists('data.'.$property['name'], $event['filterablePaths']))
                                            <flux:badge color="green" size="sm">{{ __('suggested') }}</flux:badge>
                                        @endif
                                    </x-table.td>
                                </x-table.tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-4 text-center text-sm text-zinc-400 dark:text-zinc-500">
                                        {{ __('This event does not document its properties yet (no exampleData()).') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </x-table.table>
                </div>

                <flux:text size="sm" class="text-zinc-500">
                    {{ __('Any property can be used in a rule condition. "Suggested" ones are offered in the rule form.') }}
                </flux:text>
            </x-card>
        @endif
    </div>
</div>
