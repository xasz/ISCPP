<x-layouts.app>
    <div class="grid auto-rows-min gap-4 md:grid-cols-4">            
        <x-card-simple-info title="Endpoints" value="{{ $endpointsCount['all'] }}" />
    </div>

    <x-card title="Filter">
        <form action="{{ route('scendpoints.index') }}" method="GET" >
            <x-card-details-input label="Hostname" name="filterHostname" value="{{ request('filterHostname') }}" />
            <x-a-button type="submit">Filter</x-a-button>
        </form>
    </x-card>


    <x-card>
        <x-table.table>
            <x-table.thead>
                <tr>
                    <x-table.th>Id</x-table.th>
                    <x-table.th>Hostname</x-table.th>
                    <x-table.th>Type</x-table.th>
                    <x-table.th>Health</x-table.th>
                    <x-table.th>Tamper Protection</x-table.th>
                    <x-table.th>Last Seen</x-table.th>
                </tr>
            </x-table.thead>
            <tbody>
                @foreach ($scendpoints as $endpoint)
                    <x-table.tr>
                        <x-table.td>{{ $endpoint->id }}</x-table.td>
                        <x-table.td>{{ $endpoint->hostname }}</x-table.td>
                        <x-table.td>{{ $endpoint->type }}</x-table.td>
                        <x-table.td>{{ $endpoint->healthStatus }}</x-table.td>
                        <x-table.td>{{ $endpoint->tamperProtectionEnabled }}</x-table.td>
                        <x-table.td>{{ $endpoint->lastSeen }}</x-table.td>
                    </x-table.tr>
                @endforeach
            </tbody>
        </x-table.table>
        <div class="py-4">
            {{ $scendpoints->appends(request()->query())->links() }}
        </div>
    </x-card>
</x-layouts.app>