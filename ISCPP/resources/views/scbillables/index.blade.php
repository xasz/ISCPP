<x-layouts.app>

    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        @php
            $date = Carbon\Carbon::createFromDate($year, $month)->startOfMonth();
            $back = $date->clone()->subMonth();
            $forward = $date->clone()->addMonth();

        @endphp 
        <x-card-simple-info title="Month" value="{{ $month }}" />
        <x-card-simple-info title="Year" value="{{ $year }}" />
        <x-card>
            <div class="grid auto-rows-min gap-4 md:grid-cols-2">
                <x-a-button href="{{ route('scbillables.index', ['month' => $back->month, 'year' => $back->year]) }}">
                    {{ __('Back:') . ' ' }}  {{ $back->format('m-Y'); }}
                </x-a-button>
                <x-a-button href="{{ route('scbillables.index', ['month' => $forward->month, 'year' => $forward->year]) }}">
                    {{ __('Next:') . ' ' }} {{ $forward->format('m-Y'); }}
                </x-a-button>
            </div>
        </x-card>
    </div>
    <x-card>
        <x-table.table>
            <thead>
                <tr>
                    <x-table.th>Order Line Item Number</x-table.th>
                    <x-table.th>Product Group</x-table.th>
                    <x-table.th>Billable Quantity</x-table.th>
                    <x-table.th>Ordered Quantity</x-table.th>
                    <x-table.th>Actual Quantity</x-table.th>
                    <x-table.th>Product Code</x-table.th>
                    <x-table.th>SKU</x-table.th>
                    <x-table.th>Product Description</x-table.th>
                    <x-table.th>Account Id</x-table.th>
                </tr>
            </thead>
            <tbody>
                @foreach($scbillables as $data)
                    <x-table.tr>
                        <x-table.td>{{ $data['orderLineItemNumber'] }}</x-table.td>
                        <x-table.td>{{ $data['productGroup'] }}</x-table.td>
                        <x-table.td>{{ $data['billableQuantity'] }}</x-table.td>
                        <x-table.td>{{ $data['orderedQuantity'] }}</x-table.td>
                        <x-table.td>{{ $data['actualQuantity'] }}</x-table.td>
                        <x-table.td>{{ $data['productCode'] }}</x-table.td>
                        <x-table.td>{{ $data['sku'] }}</x-table.td>
                        <x-table.td>{{ $data['productDescription'] }}</x-table.td>
                        <x-table.td>
                            <x-table.a href="{{ route('sctenants.show', $data['tenantId']) }}">
                            {{ $data->SCTenant->name }}
                            </x-table.a>
                        </x-table.td>
                        <x-table.td>
                            <x-table.a href="{{ route('scbillables.show', $data['id']) }}">
                            {{ __('Details') }}
                            </x-table.a>
                        </x-table.td>
                    </x-table.tr>
                    @endforeach
            </tbody>
        </x-table.table>
        <div class="py-4">
            {{ $scbillables->links() }}
        </div>
    </x-card>
</x-layouts.app>