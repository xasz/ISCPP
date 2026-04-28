@props(['scfirewalls', 'hideSCTenant' => false])
<div class="relative overflow-x-auto">
    <x-table.table>
        <x-table.thead>
            <tr>
                <x-table.th>Hostname</x-table.th>
                <x-table.th>Serial Number</x-table.th>
                <x-table.th>Firmware Version</x-table.th>
                <x-table.th>Connected</x-table.th>
                <x-table.th>Suspended</x-table.th>
                <x-table.th>Updated At</x-table.th>
                @unless ($hideSCTenant)
                    <x-table.th>Tenant</x-table.th>
                @endunless
            </tr>
        </x-table.thead>
        <tbody>
            @foreach ($scfirewalls as $firewall)
                <x-table.tr>
                    <x-table.td>
                        <x-table.a href="{{ route('scfirewalls.firewallDetails', ['id' => $firewall->id]) }}">
                            {{ $firewall->hostname }}
                        </x-table.a>
                    </x-table.td>
                    <x-table.td>{{ data_get($firewall->rawData, 'serialNumber', __('N/A')) }}</x-table.td>
                    <x-table.td>{{ data_get($firewall->rawData, 'firmwareVersion', __('N/A')) }}</x-table.td>
                    <x-table.td>{{ data_get($firewall->rawData, 'status.connected') ? __('Yes') : __('No') }}</x-table.td>
                    <x-table.td>{{ data_get($firewall->rawData, 'status.suspended') ? __('Yes') : __('No') }}</x-table.td>
                    <x-table.td>{{ data_get($firewall->rawData, 'updatedAt', __('N/A')) }}</x-table.td>
                    @unless ($hideSCTenant)
                        <x-table.td>{{ $firewall->SCTenant?->name ?? __('Unknown') }}</x-table.td>
                    @endunless
                </x-table.tr>
            @endforeach
        </tbody>
    </x-table.table>

    @if($scfirewalls->hasPages())
        <div class="px-4 py-3 border-t border-neutral-100 dark:border-neutral-800">
            {{ $scfirewalls->appends(request()->query())->links() }}
        </div>
    @endif
</div>
