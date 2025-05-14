<x-layouts.app>
    <x-table.table>
        <x-table.thead>
            <tr>
                <x-table.th>Created at</x-table.th>
                <x-table.th>Url</x-table.th>
                <x-table.th>statusCode</x-table.th>
                <x-table.th>Response</x-table.th>
                <x-table.th>Payload</x-table.th>
                <x-table.th>SC Alert ID</x-table.th>
            </tr>
        </x-table.thead>
        <tbody>
            @foreach ($webhookLogs as $webhookLog)
                <x-table.tr>
                    <x-table.td>{{ \App\Services\ISCPPFormat::formatDateWithSeconds($webhookLog->created_at) }}</x-table.td>
                    <x-table.td>{{ $webhookLog->url }}</x-table.td>
                    <x-table.td>{{ $webhookLog->statusCode }}</x-table.td>
                    <x-table.td>{{ $webhookLog->response }}</x-table.td>
                    <x-table.td>{{ json_encode($webhookLog->payload) }}</x-table.td>
                    <x-table.td>
                        <x-table.a href="{{ route('scalerts.show', $webhookLog->sc_alert_id) }}">
                            {{ $webhookLog->sc_alert_id }}
                        </x-table.a>
                    </x-table.td>   
                </x-table.tr>
            @endforeach
        </tbody>
    </x-table.table>
    <div class="py-4">
        {{ $webhookLogs->links() }}
    </div>
</x-layouts.app>