<x-layouts.app>
    <div class="flex flex-col gap-4">
        <x-card :title="$event->label()" :subtitle="$event->key">
            <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
                <x-card-details-row label="{{ __('Occurred at') }}" :value="\App\Services\ISCPPFormat::formatDateWithSeconds($event->occurred_at)" />
                <x-card-details-row label="{{ __('Severity') }}" :value="$event->severity->value" />
                <x-card-details-row label="{{ __('Event ID') }}" :value="$event->uuid" />
            </div>

            <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
                <div class="flex flex-col gap-0.5 py-1.5">
                    <flux:text size="xs" class="font-medium tracking-wide text-zinc-400 uppercase dark:text-zinc-500">{{ __('Tenant') }}</flux:text>
                    @if($event->SCTenant)
                        <x-table.a href="{{ route('sctenants.tenantDetails', $event->SCTenant) }}">{{ $event->SCTenant->name }}</x-table.a>
                    @else
                        <flux:text class="text-zinc-800 dark:text-zinc-200">{{ $event->tenantId ?? __('Global') }}</flux:text>
                    @endif
                </div>
                <x-card-details-row label="{{ __('Subject') }}" :value="$event->subject_type ?? '-'" />
                <x-card-details-row label="{{ __('Subject ID') }}" :value="$event->subject_id ?? '-'" />
            </div>

            <x-card-details-json :arr="$event->payload" />
        </x-card>

        <x-card title="{{ __('Deliveries') }}" subtitle="{{ __('Webhook deliveries triggered by this event') }}">
            <div class="relative overflow-x-auto">
                <x-table.table>
                    <x-table.thead>
                        <tr>
                            <x-table.th compact>{{ __('Time') }}</x-table.th>
                            <x-table.th compact>{{ __('Rule') }}</x-table.th>
                            <x-table.th compact>{{ __('URL') }}</x-table.th>
                            <x-table.th compact>{{ __('Attempt') }}</x-table.th>
                            <x-table.th compact>{{ __('Status') }}</x-table.th>
                            <x-table.th compact>{{ __('Response') }}</x-table.th>
                        </tr>
                    </x-table.thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($event->deliveries as $delivery)
                            <x-table.tr>
                                <x-table.td compact class="whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                                    {{ \App\Services\ISCPPFormat::formatDateWithSeconds($delivery->created_at) }}
                                </x-table.td>
                                <x-table.td compact>{{ $delivery->rule?->name ?? '-' }}</x-table.td>
                                <x-table.td compact class="break-all text-xs">{{ $delivery->url ?? '-' }}</x-table.td>
                                <x-table.td compact>{{ $delivery->attempt }}</x-table.td>
                                <x-table.td compact>
                                    <flux:badge color="{{ $delivery->badgeColor() }}" size="sm">{{ $delivery->status }}</flux:badge>
                                    @if($delivery->http_status)
                                        <span class="text-xs text-zinc-400">{{ $delivery->http_status }}</span>
                                    @endif
                                </x-table.td>
                                <x-table.td compact class="whitespace-normal break-all text-xs">
                                    {{ Str::limit($delivery->error ?? $delivery->response_body ?? '', 200) }}
                                </x-table.td>
                            </x-table.tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-zinc-400 dark:text-zinc-500">
                                    {{ __('No rule matched this event, so nothing was delivered.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-table.table>
            </div>
        </x-card>
    </div>
</x-layouts.app>
