<x-layouts.app>
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
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
        <form action="{{ route('sctenants.index') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <div class="min-w-56 flex-1">
                <x-card-details-input label="Name" name="filterTenantName" value="{{ request('filterTenantName') }}" />
            </div>
            <flux:button type="submit" variant="primary">{{ __('Filter') }}</flux:button>
            @if(request()->hasAny(['filterTenantName', 'filterTenantType']))
                <flux:button href="{{ route('sctenants.index') }}" variant="ghost">{{ __('Reset') }}</flux:button>
            @endif
        </form>
    </x-card>

    <x-card title="Tenants" subtitle="{{ $sctenants->total() }} total">
        <x-table.table>
            <x-table.thead>
                <tr>
                    <x-table.th>Name</x-table.th>
                    <x-table.th>Data Geography</x-table.th>
                    <x-table.th>Data Region</x-table.th>
                    <x-table.th>Billing Type</x-table.th>
                </tr>
            </x-table.thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($sctenants as $tenant)
                    <x-table.tr>
                        <x-table.td>
                                <x-table.a href="{{ route('sctenants.tenantDetails', $tenant) }}">
                                    {{ $tenant->name }}
                                </x-table.a>
                                <br>
                                <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ __('ShowAs: ') }}{{ $tenant->showAs }}</span>
                        </x-table.td>
                        <x-table.td>{{ $tenant->dataGeography }}</x-table.td>
                        <x-table.td>{{ $tenant->dataRegion }}</x-table.td>
                        <x-table.td><flux:badge size="sm" color="zinc">{{ ucfirst($tenant->billingType) }}</flux:badge></x-table.td>
                    </x-table.tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-zinc-400 dark:text-zinc-500">
                            {{ __('No tenants found.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-table.table>
        <div class="py-4">
            {{ $sctenants->appends(request()->query())->links() }}
        </div>
    </x-card>
</x-layouts.app>
