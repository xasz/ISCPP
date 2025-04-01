<x-layouts.app>
    <div class="grid auto-rows-min gap-4 md:grid-cols-4">            
        <x-card-simple-info title="Endpoints" value="{{ $endpointsCount['all'] }}" />
    </div>
    <x-card>
        <x-table.table>
            <x-table.thead>
                <tr>
                    <x-table.th>Id</x-table.th>
                    <x-table.th>Hostname</x-table.th>
                </tr>
            </x-table.thead>
            <tbody>
                @foreach ($scendpoints as $endpoint)
                    <x-table.tr>
                        <x-table.td>{{ $endpoint->id }}</x-table.td>
                        <x-table.td>{{ $endpoint->hostname }}</x-table.td>
                    </x-table.tr>
                @endforeach
            </tbody>
        </x-table.table>
        <div class="py-4">
            {{ $scendpoints->links() }}
        </div>
    </x-card>
</x-layouts.app>