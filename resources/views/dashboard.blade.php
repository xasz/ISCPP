<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <x-card-simple-info title="Tenants" value="{{ $tenantsCount }}" />
            <x-card-simple-info title="Jobs in Queue" value="{{ $jobsInQueue }}" />
            <x-card-simple-info title="Alerts last 24h" value="{{ $alerts24HCount }}" />
        </div>
        <x-card class="size-full" title="Awareness" subtitle="Here you see some hints you should beaware of">
            <x-table>
                <x-table.thead>
                    <x-table.th>Message</x-table.th>
                </x-table.thead>
                @foreach ($awareness as $aware)
                    <x-table.tr>
                        <x-table.td><flux:badge class="mr-2" color="amber">!</flux:badge> {{ $aware['message'] }}</x-table.td>
                    </x-table.tr>
                @endforeach
            </x-table>
        </x-card>
    </div>
</x-layouts.app>
