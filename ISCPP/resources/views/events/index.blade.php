<x-layouts.app>
    <x-card>
        <div>
        <x-a-button href="{{ route('events.index', ['hideInfo' => $hideInfo]) }}" class="mr-2">Aktualisieren</x-a-button>

        @if($hideInfo)
            <x-a-button href="{{ route('events.index') }}" class="mr-2">Alle anzeigen</x-a-button>
        @else
            <x-a-button href="{{ route('events.index', ['hideInfo' => 1]) }}" class="mr-2">Info ausblenden</x-a-button>
        @endif
        </div>
        <x-table.table>
            <x-table.thead>
                <tr>
                    <x-table.th>Time</x-table.th>
                    <x-table.th>Event</x-table.th>
                    <x-table.th>Type</x-table.th>
                    <x-table.th>Info</x-table.th>
                </tr>
            </x-table.thead>
            <tbody>
                @foreach ($events as $event)
                    <x-table.tr>
                        <x-table.td>{{ \App\Services\ISCPPFormat::formatDateWithSeconds($event->created_at) }}</x-table.td>
                        <x-table.td>{{ $event->event }}</x-table.td>
                        <x-table.td>
                        @php
                            switch($event->type) {
                                case 'info':
                                    $color = 'bg-blue-500';
                                    break;
                                case 'warning':
                                    $color = 'bg-yellow-500';
                                    break;
                                case 'error':
                                    $color = 'bg-red-500';
                                    break;
                                default:
                                    $color = 'bg-gray-500';
                            }
                        @endphp
                        <div class="text-m px-2 py-0.5 center text-center rounded-xl {{ $color }}">
                            {{ $event->type }}
                        </div>
                        </x-table.td>
                        <x-table.td>
                            @if($event->data == null)
                                {{ __('No data') }}
                            @else
                                @foreach ($event->data as $key => $value)
                                <div class="text-xs">
                                    <span class="font-semibold">{{ $key }}:</span>{{ json_encode($value) }}
                                </div>    
                                @endforeach
                            @endif
                        </x-table.td>
                    </x-table.tr>
                @endforeach
            </tbody>
        </x-table.table>
        <div class="py-4">
            {{ $events->appends(request()->query())->links()}}
        </div>
    </x-card>
</x-layouts.app>