<x-layouts.app>
    <x-card title="ISCPP Events" subtitle="Every event raised by the ISCPP event system">
        <form method="GET" action="{{ route('iscpp.events.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="w-64">
                <flux:select name="key" label="Event" size="sm">
                    <option value="">{{ __('All events') }}</option>
                    @foreach($eventOptions as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['key'] ?? null) === $key)>{{ $label }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div class="w-40">
                <flux:select name="severity" label="Severity" size="sm">
                    <option value="">{{ __('All') }}</option>
                    @foreach(['info', 'warning', 'error', 'critical'] as $severity)
                        <option value="{{ $severity }}" @selected(($filters['severity'] ?? null) === $severity)>{{ ucfirst($severity) }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div class="w-72">
                <flux:select name="tenantId" label="Tenant" size="sm">
                    <option value="">{{ __('All tenants') }}</option>
                    @foreach($sctenants as $sctenant)
                        <option value="{{ $sctenant->id }}" @selected(($filters['tenantId'] ?? null) === $sctenant->id)>{{ $sctenant->name }}</option>
                    @endforeach
                </flux:select>
            </div>

            <x-a-button type="submit">{{ __('Filter') }}</x-a-button>

            @if(array_filter($filters))
                <flux:button href="{{ route('iscpp.events.index') }}" variant="ghost" size="sm">{{ __('Reset') }}</flux:button>
            @endif
        </form>

        <div class="relative overflow-x-auto">
            <x-table.table>
                <x-table.thead>
                    <tr>
                        <x-table.th compact class="w-1/6">{{ __('Time') }}</x-table.th>
                        <x-table.th compact class="w-24">{{ __('Severity') }}</x-table.th>
                        <x-table.th compact class="w-1/6">{{ __('Event') }}</x-table.th>
                        <x-table.th compact class="w-1/6">{{ __('Tenant') }}</x-table.th>
                        <x-table.th compact>{{ __('Data') }}</x-table.th>
                        <x-table.th compact class="w-20"></x-table.th>
                    </tr>
                </x-table.thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($events as $event)
                        <x-table.tr>
                            <x-table.td compact class="whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                                {{ \App\Services\ISCPPFormat::formatDateWithSeconds($event->occurred_at) }}
                            </x-table.td>
                            <x-table.td compact>
                                <flux:badge color="{{ $event->severity->badgeColor() }}" size="sm">{{ $event->severity->value }}</flux:badge>
                            </x-table.td>
                            <x-table.td compact class="font-medium text-zinc-800 dark:text-zinc-200">
                                {{ $event->label() }}
                                <div class="text-xs text-zinc-400 dark:text-zinc-500">{{ $event->key }}</div>
                            </x-table.td>
                            <x-table.td compact>
                                @if($event->SCTenant)
                                    <x-table.a href="{{ route('sctenants.tenantDetails', $event->SCTenant) }}">{{ $event->SCTenant->name }}</x-table.a>
                                @elseif($event->tenantId)
                                    <span class="text-zinc-400 dark:text-zinc-500">{{ $event->tenantId }}</span>
                                @else
                                    <span class="text-zinc-400 dark:text-zinc-500">{{ __('Global') }}</span>
                                @endif
                            </x-table.td>
                            <x-table.td compact class="whitespace-normal">
                                <div class="flex flex-wrap gap-x-4 gap-y-0.5">
                                    @forelse ($event->data() as $key => $value)
                                        <span>
                                            <span class="font-semibold text-zinc-500 dark:text-zinc-400">{{ $key }}:</span>
                                            {{ is_scalar($value) ? $value : json_encode($value) }}
                                        </span>
                                    @empty
                                        <span class="text-zinc-400 dark:text-zinc-500">{{ __('No data') }}</span>
                                    @endforelse
                                </div>
                            </x-table.td>
                            <x-table.td compact>
                                <x-table.a href="{{ route('iscpp.events.show', $event) }}">{{ __('Details') }}</x-table.a>
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-400 dark:text-zinc-500">
                                {{ __('No ISCPP events recorded yet.') }}
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
