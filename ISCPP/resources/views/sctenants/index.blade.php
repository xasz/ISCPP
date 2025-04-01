<x-layouts.app>
    <div class="grid auto-rows-min gap-4 md:grid-cols-4">            
        <x-card-simple-info title="Tenants" value="{{ $tenantsCount['all'] }}" />
        <x-card-simple-info title="Usage" value="{{ $tenantsCount['usage'] }}" />
        <x-card-simple-info title="Term" value="{{ $tenantsCount['term'] }}" />
        <x-card-simple-info title="Trail" value="{{ $tenantsCount['trail'] }}" />
    </div>
    <x-card>
        <x-table.table>
            <x-table.thead>
                <tr>
                    <x-table.th>Name</x-table.th>
                    <x-table.th>Data Geography</x-table.th>
                    <x-table.th>Data Region</x-table.th>
                    <x-table.th>Billing Type</x-table.th>
                </tr>
            </x-table.thead>
            <tbody>
                @foreach ($sctenants as $tenant)
                    <x-table.tr>
                        <x-table.td>
                                <x-table.a href="{{ route('sctenants.show', $tenant) }}">
                                    {{ $tenant->name }}
                                </x-table.a>
                                <br>
                                <span class="text-xs">{{ __('ShowAs: ') }} {{ $tenant->showAs }} </span>
                        </x-table.td>    
                        <x-table.td>{{ $tenant->dataGeography }}</x-table.td>
                        <x-table.td>{{ $tenant->dataRegion }}</x-table.td>
                        <x-table.td>{{ $tenant->billingType }}</x-table.td>
                    </x-table.tr>
                @endforeach
            </tbody>
        </x-table.table>
        <div class="py-4">
            {{ $sctenants->links() }}
        </div>
    </x-card>
</x-layouts.app>