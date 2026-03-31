@props(['scalerts', 'hideSCTenant' => false])

{{-- Tailwind safelist for dynamic severity colors --}}
<span class="bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400
             bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400
             bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400
             bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-400 hidden"></span>

<div class="overflow-x-auto">
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
