<x-layouts.app>
    <x-card title="System Events" subtitle="Recent events raised across the application">
        <div class="flex items-center gap-2">
            <flux:button href="{{ route('events.index', ['hideInfo' => $hideInfo]) }}" icon="arrow-path" size="sm">
                {{ __('Aktualisieren') }}
            </flux:button>

            @if($hideInfo)
                <flux:button href="{{ route('events.index') }}" variant="ghost" size="sm">
                    {{ __('Alle anzeigen') }}
                </flux:button>
            @else
                <flux:button href="{{ route('events.index', ['hideInfo' => 1]) }}" variant="ghost" size="sm">
                    {{ __('Info ausblenden') }}
                </flux:button>
            @endif
        </div>

        <div class="relative overflow-x-auto">
            <x-table.table>
                <x-table.thead>
                    <tr>
                        <x-table.th compact class="w-1/6">Time</x-table.th>
                        <x-table.th compact class="w-1/12">Type</x-table.th>
                        <x-table.th compact class="w-1/4">Event</x-table.th>
                        <x-table.th compact>Info</x-table.th>
                    </tr>
                </x-table.thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($events as $event)
                        <x-table.tr>
                            <x-table.td compact class="whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                                {{ \App\Services\ISCPPFormat::formatDateWithSeconds($event->created_at) }}
                            </x-table.td>
                            <x-table.td compact>
                                @php
                                    $color = match($event->type) {
                                        'info' => 'blue',
                                        'warning' => 'amber',
                                        'error' => 'red',
                                        default => 'zinc',
                                    };
                                @endphp
                                <flux:badge color="{{ $color }}" size="sm">{{ $event->type }}</flux:badge>
                            </x-table.td>
                            <x-table.td compact class="font-medium text-zinc-800 dark:text-zinc-200">
                                {{ $event->event }}
                            </x-table.td>
                            <x-table.td compact class="whitespace-normal">
                                @if($event->data == null)
                                    <span class="text-zinc-400 dark:text-zinc-500">{{ __('No data') }}</span>
                                @else
                                    <div class="flex flex-wrap gap-x-4 gap-y-0.5">
                                        @foreach ($event->data as $key => $value)
                                            @switch($key)
                                                @case('SCTenant')
                                                    <span>
                                                        <span class="font-semibold text-zinc-500 dark:text-zinc-400">{{ $key }}:</span>
                                                        <a class="text-accent-content hover:underline" href="{{ route('sctenants.tenantDetails', ['sctenant' => $value]) }}">{{ json_encode($value) }}</a>
                                                    </span>
                                                @break
                                                @case('SCAlert')
                                                    <span>
                                                        <span class="font-semibold text-zinc-500 dark:text-zinc-400">{{ $key }}:</span>
                                                        <a class="text-accent-content hover:underline" href="{{ route('scalerts.alertDetails', ['id' => $value]) }}">{{ json_encode($value) }}</a>
                                                    </span>
                                                @break
                                                @default
                                                    <span>
                                                        <span class="font-semibold text-zinc-500 dark:text-zinc-400">{{ $key }}:</span>
                                                        {{ json_encode($value) }}
                                                    </span>
                                                @break
                                            @endswitch
                                        @endforeach
                                    </div>
                                @endif
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-zinc-400 dark:text-zinc-500">
                                {{ __('No events found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table.table>
        </div>

        <div class="py-2">
            {{ $events->appends(request()->query())->links() }}
        </div>
    </x-card>
</x-layouts.app>
