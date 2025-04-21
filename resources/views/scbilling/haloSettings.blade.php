<x-layouts.app>
    <x-card title="ISCPP Tenants with no matched haloclient_id" subtitle="For easy billing, please match all clients">
        <x-table>
            <x-table.thead>
                <x-table.th>SCTenant</x-table.th>
                <x-table.th>Halo Client ID</x-table.th>
            </x-table.thead>
            @foreach ($SCTenants as $SCTenant)
                <x-table.tr>   
                    <x-table.td>
                        <x-table.a href="{{ route('sctenants.show', $SCTenant) }}">
                            {{ $SCTenant->name }}
                        </x-table.a>
                        <br>
                        <span class="text-xs">{{ __('ShowAs: ') }} {{ $SCTenant->showAs }} </span>
                    </x-table.td>
                    <x-table.td>{{ $SCTenant['haloclient_id'] }}</x-table.td>
                </x-table.tr>
            @endforeach
        </x-table>
    </x-card>
</x-layouts.app>