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
        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
            @forelse ($scalerts as $scalert)
                <x-table.tr>
                    <x-table.td>
                        @php
                            $color = match(strtolower($scalert->severity ?? '')) {
                                'high'   => 'red',
                                'medium' => 'yellow',
                                'low'    => 'blue',
                                default  => 'neutral',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-{{ $color }}-100 text-{{ $color }}-700 dark:bg-{{ $color }}-900/40 dark:text-{{ $color }}-400">
                            {{ $scalert->severity }}
                        </span>
                    </x-table.td>
                    <x-table.td>
                        <x-table.a href="{{ route('scalerts.show', $scalert) }}">
                            {{ $scalert->description }}
                        </x-table.a>
                    </x-table.td>
                    <x-table.td class="whitespace-nowrap px-4 py-3 text-neutral-500 dark:text-neutral-400 text-xs">
                        {{ $scalert->raisedAt }}
                    </x-table.td>
                    <x-table.td>{{ $scalert->type }}</x-table.td>
                    <x-table.td>{{ $scalert->category }}</x-table.td>
                    <x-table.td>{{ $scalert->product }}</x-table.td>
                    @unless ($hideSCTenant)
                    <x-table.td>
                        {{ $scalert->sctenant != null ? $scalert->sctenant->name : __('Unknown') }}
                    </x-table.td>
                    @endunless
                </x-table.tr>
            @empty
                <tr>
                    <td colspan="{{ $hideSCTenant ? 6 : 7 }}" class="px-4 py-8 text-center text-sm text-neutral-400 dark:text-neutral-500">
                        {{ __('No alerts found.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-table.table>

    @if($scalerts->hasPages())
    <div class="px-4 py-3 border-t border-neutral-100 dark:border-neutral-800">
        {{ $scalerts->links() }}
    </div>
    @endif
</div>
