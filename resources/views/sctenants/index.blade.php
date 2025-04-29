<x-layouts.app>
    <div class="grid grid-cols-4 gap-4">            
        <x-card-simple-info title="Tenants" value="{{ $tenantsCount['all'] }}">
            <x-slot name="menu">
                <flux:menu.item href="{{ route('sctenants.index',[ 'filterTenantType' => '']) }}">Filter</flux:menu.item>
            </x-slot>
        </x-card-simple-info>
        <x-card-simple-info title="Usage" value="{{ $tenantsCount['usage'] }}">
            <x-slot name="menu">
                <flux:menu.item href="{{ route('sctenants.index',[ 'filterTenantType' => 'usage']) }}">Filter</flux:menu.item>
            </x-slot>
        </x-card-simple-info>
        <x-card-simple-info title="Term" value="{{ $tenantsCount['term'] }}">
            <x-slot name="menu">
                <flux:menu.item href="{{ route('sctenants.index',[ 'filterTenantType' => 'term']) }}">Filter</flux:menu.item>
            </x-slot>
        </x-card-simple-info>
        <x-card-simple-info title="Trail" value="{{ $tenantsCount['trail'] }}" >
            <x-slot name="menu">
                <flux:menu.item href="{{ route('sctenants.index',[ 'filterTenantType' => 'trail']) }}">Filter</flux:menu.item>
            </x-slot>
        </x-card-simple-info>
    </div>

    <x-card title="Filter">
        <form action="{{ route('sctenants.index') }}" method="GET" >
            <x-card-details-input label="Name" name="filterTenantName" value="{{ request('filterTenantName') }}" />
            <x-a-button type="submit">Filter</x-a-button>
        </form>
    </x-card>

    <x-card title="Tenants">
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
            {{ $sctenants->appends(request()->query())->links() }}
        </div>
    </x-card>
</x-layouts.app>