@props(['scalerts', 'hideSCTenant' => false])
<div class="relative overflow-x-auto">
    <x-table.table>
        <x-table.thead>
            <tr>
                @if($hideSCTenant)
                    <x-table.th class="w-1/16">Severity</x-table.th>
                    <x-table.th class="w-6/16">Description</x-table.th>
                    <x-table.th class="w-2/16">Raised at</x-table.th>
                    <x-table.th class="w-4/16">Type</x-table.th>
                    <x-table.th class="w-1/16">Category</x-table.th>
                    <x-table.th class="w-1/16">Product</x-table.th>
                @else
                    <x-table.th class="w-1/16">Severity</x-table.th>
                    <x-table.th class="w-5/16">Description</x-table.th>
                    <x-table.th class="w-2/16">Raised at</x-table.th>
                    <x-table.th class="w-3/16">Type</x-table.th>
                    <x-table.th class="w-1/16">Category</x-table.th>
                    <x-table.th class="w-1/16">Product</x-table.th>
                    <x-table.th class="w-2/16">Tenant</x-table.th>
                @endif

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