@props(['scalerts', 'hideSCTenant' => false])
<div class="relative overflow-x-auto">
    <span class="text-red-600 bg-red-600 hidden">Tailwind Cache</span>
    <span class="text-yellow-600 bg-yellow-600 hidden">Tailwind Cache</span>
    <span class="text-grey-600 bg-grey-600 hidden">Tailwind Cache</span>
    <x-table.table>
        <x-table.thead>
            <tr>
            <x-table.th>Severity</x-table.th>
                <x-table.th>Description</x-table.th>
                <x-table.th>Raised at</x-table.th>
                <x-table.th>Type</x-table.th>
                <x-table.th>Category</x-table.th>
                <x-table.th>Product</x-table.th>
                @unless ($hideSCTenant)
                <x-table.th>Tenant</x-table.th>
                @endunless
            </tr>
        </x-table.thead>
        <tbody>
            @foreach ($scalerts as $scalert)
                <x-table.tr>
                    <x-table.td>    
                        <div class="px-3 py-1 center text-center rounded-xl bg-{{ $scalert->getColorTailwindColor() }}">
                            {{ $scalert->severity }}
                        </div>
                    </x-table.td>
                    <x-table.td>
                        <x-table.a href="{{ route('scalerts.show', $scalert) }}">
                            {{ $scalert->description }}
                        </x-table.a>
                    </x-table.td><x-table.td>{{ $scalert->raisedAt }}</x-table.td>
                    <x-table.td>{{ $scalert->type }}</x-table.td>    
                    <x-table.td>{{ $scalert->category }}</x-table.td>
                    <x-table.td>{{ $scalert->product }}</x-table.td>
                    @unless ($hideSCTenant)
                    <x-table.td>{{ $scalert->sctenant != null ? $scalert->sctenant->name : __('Unkown')}}</x-table.td>
                    @endunless
                </x-table.tr>
            @endforeach
        </tbody>
    </x-table.table>
    <div class="py-4">
        {{ $scalerts->links() }}
    </div>
</div>