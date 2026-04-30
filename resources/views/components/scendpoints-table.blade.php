@props(['scendpoints', 'hideSCTenant' => false])
<div class="relative overflow-x-auto">
    <x-table.table>
        <x-table.thead>
            <tr>
                <x-table.th>Hostname</x-table.th>
                <x-table.th>Type</x-table.th>
                <x-table.th>Health</x-table.th>
                <x-table.th>Tamper Protection</x-table.th>
                <x-table.th>Last Seen</x-table.th>
                @unless ($hideSCTenant)
                    <x-table.th>Tenant</x-table.th>
                @endunless
            </tr>
        </x-table.thead>
        <tbody>
            @foreach ($scendpoints as $endpoint)
                <x-table.tr>
                    <x-table.td>
                        <x-table.a href="{{ route('scendpoints.endpointDetails', ['id' => $endpoint->id]) }}">
                            {{ $endpoint->hostname }}
                        </x-table.a>
                    </x-table.td>
                    <x-table.td>{{ $endpoint->type }}</x-table.td>
                    <x-table.td>{{ $endpoint->healthStatus }}</x-table.td>
                    <x-table.td>{{ $endpoint->tamperProtectionEnabled ? __('Enabled') : __('Disabled') }}</x-table.td>
                    <x-table.td>{{ $endpoint->lastSeen }}</x-table.td>
                    @unless ($hideSCTenant)
                        <x-table.td>{{ $endpoint->SCTenant?->name ?? __('Unknown') }}</x-table.td>
                    @endunless
                </x-table.tr>
            @endforeach
        </tbody>
    </x-table.table>

    @if($scendpoints->hasPages())
        <div class="px-4 py-3 border-t border-neutral-100 dark:border-neutral-800">
            {{ $scendpoints->appends(request()->query())->links() }}
        </div>
    @endif
</div>
