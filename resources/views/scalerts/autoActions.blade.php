<x-layouts.app>
    <livewire:tools.scalerts-autoactions />

    <x-card class="mt-8" title="CurrentAvailable SCAlert Types">
        <x-table>
            <x-table.thead>
                <x-table.th>Type</x-table.th>
                <x-table.th>Count</x-table.th>
            </x-table.thead>
            <tbody>
                        @foreach(\App\Models\SCAlert::query()->select('type')->groupBy('type')->selectRaw('type, COUNT(*) as count')->orderByDesc('count')->get() as $row)
                    <x-table.tr>
                        <x-table.td>{{ $row->type }}</x-table.td>
                        <x-table.td>{{ $row->count }}</x-table.td>
                    </x-table.tr>
                @endforeach
            </tbody>
        </x-table>
    </x-card>
</x-layouts.app>