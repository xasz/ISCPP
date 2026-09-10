<x-layouts.app>
    <x-card title="Tenants">
        @php
            $scores = \App\Models\SCTenantHealthscore::AvailableScores();
        @endphp

        <div class="relative overflow-x-auto">
            <x-table.table>
                <x-table.thead>
                    <tr>
                        <x-table.th class="max-w-100" >SCTenant</x-table.th>
                        @foreach($scores as $score)
                            <x-table.th class="w-40 max-w-40 min-w-40 text-tiny">{{ Str::replace('.', ' ', $score) }}</x-table.th>
                        @endforeach
                    </tr>
                </x-table.thead>
                <tbody>
                    @foreach ($sctenants as $sctenant)
                        <x-table.tr>
                            <x-table.td class="max-w-100">
                                    <x-table.a href="{{ route('sctenants.tenantDetails', $sctenant) }}">
                                        {{ $sctenant->name }}
                                    </x-table.a>
                                    <br>
                                    <span class="text-xs">{{ __('ShowAs: ') }} {{ $sctenant->showAs }} </span>
                            </x-table.td>  
                            @if($sctenant->SCTenantHealthscore == null)
                                @foreach($scores as $score)
                                    <x-table.td></x-table.td>
                                @endforeach
                            @else
                                @foreach ($scores as $score)
                                    @php
                                        $tenantScores = $sctenant->SCTenantHealthscore->getScores();
                                    @endphp
                                    @if($tenantScores[$score] >= 0)
                                        <x-table.td class="w-40 max-w-40 min-w-40">
                                            <div class="flex justify-center">
                                                <x-healthscore-badge :score="$tenantScores[$score]" />
                                            </div>
                                        </x-table.td>
                                    @else
                                        <x-table.td></x-table.td>
                                    @endif
                                @endforeach
                            @endif  
                                    
                        </x-table.tr>
                    @endforeach
                </tbody>
            </x-table.table>
        </div>
        <div class="py-4">
            {{ $sctenants->appends(request()->query())->links() }}
        </div>
    </x-card>
</x-layouts.app>