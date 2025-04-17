@props(['scbillables', 'hideSCTenant' => false])
<div class="relative overflow-x-auto">
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
                @if(!$hideSCTenant)
                <x-table.td>
                    <x-table.a href="{{ route('sctenants.show', $data['tenantId']) }}">
                    {{ $data->SCTenant->name }}
                    </x-table.a>
                </x-table.td>
                @endif
                <x-table.td>
                    <x-table.a href="{{ route('scbillables.show', $data['id']) }}">
                    {{ __('Details') }}
                    </x-table.a>
                </x-table.td>
            </x-table.tr>
            @endforeach
        </tbody>
    </x-table.table>
</div>