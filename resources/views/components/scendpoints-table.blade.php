@props(['scendpoints', 'hideSCTenant' => false])
<div class="relative overflow-x-auto">
    <x-table.table>
        <x-table.thead>
            <tr>
                <x-table.th compact>{{ __('Hostname') }}</x-table.th>
                <x-table.th compact class="w-28">{{ __('Type') }}</x-table.th>
                <x-table.th compact class="w-28">{{ __('Health') }}</x-table.th>
                <x-table.th compact class="w-24">{{ __('Online') }}</x-table.th>
                <x-table.th compact class="w-28">{{ __('Tamper') }}</x-table.th>
                <x-table.th compact class="w-44">{{ __('Last Seen') }}</x-table.th>
                @unless ($hideSCTenant)
                    <x-table.th compact>{{ __('Tenant') }}</x-table.th>
                @endunless
            </tr>
        </x-table.thead>
        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
            @forelse ($scendpoints as $endpoint)
                <x-table.tr>
                    <x-table.td compact>
                        <x-table.a href="{{ route('scendpoints.endpointDetails', ['id' => $endpoint->id]) }}">
                            {{ $endpoint->hostname }}
                        </x-table.a>
                        <div class="text-xs text-zinc-400 dark:text-zinc-500">{{ trim((string) data_get($endpoint->rawData, 'os.name')) }}</div>
                    </x-table.td>
                    <x-table.td compact><flux:badge size="sm" color="zinc">{{ ucfirst((string) $endpoint->type) }}</flux:badge></x-table.td>
                    <x-table.td compact><flux:badge size="sm" :color="App\Models\SCEndpoint::healthColor($endpoint->healthStatus)">{{ ucfirst((string) $endpoint->healthStatus) }}</flux:badge></x-table.td>
                    <x-table.td compact>
                        <flux:badge size="sm" :color="$endpoint->isOnline() ? 'green' : 'zinc'">{{ $endpoint->isOnline() ? __('Online') : __('Offline') }}</flux:badge>
                    </x-table.td>
                    <x-table.td compact>
                        <flux:badge size="sm" :color="$endpoint->tamperProtectionEnabled ? 'green' : 'red'">{{ $endpoint->tamperProtectionEnabled ? __('Enabled') : __('Disabled') }}</flux:badge>
                    </x-table.td>
                    <x-table.td compact class="whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                        {{ $endpoint->lastSeen ? App\Services\ISCPPFormat::formatDateWithSeconds($endpoint->lastSeen) : __('Never') }}
                    </x-table.td>
                    @unless ($hideSCTenant)
                        <x-table.td compact>
                            @if($endpoint->SCTenant)
                                <x-table.a href="{{ route('sctenants.tenantDetails', $endpoint->SCTenant) }}">{{ $endpoint->SCTenant->name }}</x-table.a>
                            @else
                                {{ __('Unknown') }}
                            @endif
                        </x-table.td>
                    @endunless
                </x-table.tr>
            @empty
                <tr>
                    <td colspan="{{ $hideSCTenant ? 6 : 7 }}" class="px-4 py-8 text-center text-sm text-zinc-400 dark:text-zinc-500">
                        {{ __('No endpoints found.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-table.table>

    @if($scendpoints->hasPages())
        <div class="px-4 py-3 border-t border-zinc-100 dark:border-zinc-800">
            {{ $scendpoints->appends(request()->query())->links() }}
        </div>
    @endif
</div>
