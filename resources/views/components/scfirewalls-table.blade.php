@props(['scfirewalls', 'hideSCTenant' => false])
<div class="relative overflow-x-auto">
    <x-table.table>
        <x-table.thead>
            <tr>
                <x-table.th compact>{{ __('Hostname') }}</x-table.th>
                <x-table.th compact>{{ __('Model') }}</x-table.th>
                <x-table.th compact>{{ __('Firmware') }}</x-table.th>
                <x-table.th compact class="w-36">{{ __('Status') }}</x-table.th>
                <x-table.th compact>{{ __('Group') }}</x-table.th>
                @unless ($hideSCTenant)
                    <x-table.th compact>{{ __('Tenant') }}</x-table.th>
                @endunless
            </tr>
        </x-table.thead>
        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
            @forelse ($scfirewalls as $firewall)
                <x-table.tr>
                    <x-table.td compact>
                        <x-table.a href="{{ route('scfirewalls.firewallDetails', ['id' => $firewall->id]) }}">
                            {{ $firewall->hostname }}
                        </x-table.a>
                        <div class="text-xs text-zinc-400 dark:text-zinc-500">{{ data_get($firewall->rawData, 'serialNumber') }}</div>
                    </x-table.td>
                    <x-table.td compact class="text-xs">{{ data_get($firewall->rawData, 'model', __('N/A')) }}</x-table.td>
                    <x-table.td compact class="text-xs">
                        {{ data_get($firewall->rawData, 'firmwareVersion', __('N/A')) }}
                        @if($firewall->plannedFirmwareUpgradeAt)
                            <flux:badge size="sm" color="sky" icon="calendar">{{ __('Upgrade planned') }}</flux:badge>
                        @endif
                    </x-table.td>
                    <x-table.td compact>
                        <flux:badge size="sm" :color="$firewall->isConnected() ? 'green' : 'red'">{{ $firewall->isConnected() ? __('Connected') : __('Disconnected') }}</flux:badge>
                        @if(data_get($firewall->rawData, 'status.suspended'))
                            <flux:badge size="sm" color="amber">{{ __('Suspended') }}</flux:badge>
                        @endif
                    </x-table.td>
                    <x-table.td compact class="text-xs">{{ data_get($firewall->rawData, 'group.name', '') }}</x-table.td>
                    @unless ($hideSCTenant)
                        <x-table.td compact>
                            @if($firewall->SCTenant)
                                <x-table.a href="{{ route('sctenants.tenantDetails', $firewall->SCTenant) }}">{{ $firewall->SCTenant->name }}</x-table.a>
                            @else
                                {{ __('Unknown') }}
                            @endif
                        </x-table.td>
                    @endunless
                </x-table.tr>
            @empty
                <tr>
                    <td colspan="{{ $hideSCTenant ? 5 : 6 }}" class="px-4 py-8 text-center text-sm text-zinc-400 dark:text-zinc-500">
                        {{ __('No firewalls found.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-table.table>

    @if($scfirewalls->hasPages())
        <div class="px-4 py-3 border-t border-zinc-100 dark:border-zinc-800">
            {{ $scfirewalls->appends(request()->query())->links() }}
        </div>
    @endif
</div>
