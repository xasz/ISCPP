<x-layouts.app>
    <x-card title="Tenants">
        @php
            $scoreGroups = \App\Models\SCTenantHealthscore::ScoreGroups();
            // Cells follow the group order, so they always line up with the grouped header.
            $scores = collect($scoreGroups)->flatMap(fn (array $groupScores) => array_keys($groupScores))->all();
        @endphp

        <div class="flex items-center gap-2 pb-2">
            {{-- Static swatch, not a real badge: a badge here would carry its own tooltip. --}}
            <span class="inline-block h-4 w-8 rounded-full ring-2 ring-red-400"></span>
            <flux:text>
                {{ __('A red outline means the score is below its threshold for that tenant. Set thresholds on the tenant Healthscore tab.') }}
            </flux:text>
        </div>

        <div class="flex items-center gap-2 pb-2">
            @if($belowThreshold)
                <flux:button href="{{ route('sctenants.healthscores') }}" variant="ghost" size="sm">
                    {{ __('Alle anzeigen') }}
                </flux:button>
            @else
                <flux:button href="{{ route('sctenants.healthscores', ['belowThreshold' => 1]) }}" variant="ghost" size="sm" icon="funnel">
                    {{ __('Nur unter Threshold') }}
                </flux:button>
            @endif
        </div>

        <div class="relative overflow-x-auto">
            <x-table.table nofull>
                <x-table.thead>
                    <tr>
                        <x-table.th rowspan="2" class="align-bottom">SCTenant</x-table.th>
                        @foreach($scoreGroups as $group => $groupScores)
                            <x-table.th colspan="{{ count($groupScores) }}" class="border-x border-b border-zinc-200 text-center dark:border-zinc-700" compact>{{ $group }}</x-table.th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($scoreGroups as $group => $groupScores)
                            @foreach($groupScores as $score => $label)
                                <x-table.th class="w-24 min-w-24 text-center text-xs font-normal" compact title="{{ $group }} – {{ $label }}">{{ $label }}</x-table.th>
                            @endforeach
                        @endforeach
                    </tr>
                </x-table.thead>
                <tbody>
                    @foreach ($sctenants as $sctenant)
                        <x-table.tr>
                            <x-table.td>
                                {{-- A fixed width block is needed: table cells ignore max-width, so truncate alone would not cut the name. --}}
                                <div class="w-64">
                                    <x-table.a class="block truncate" href="{{ route('sctenants.tenantDetails', $sctenant) }}" title="{{ $sctenant->name }}">
                                        {{ $sctenant->name }}
                                    </x-table.a>
                                    <span class="block truncate text-xs" title="{{ $sctenant->showAs }}">{{ __('ShowAs: ') }} {{ $sctenant->showAs }}</span>
                                </div>
                            </x-table.td>
                            @if($sctenant->SCTenantHealthscore == null)
                                @foreach($scores as $score)
                                    <x-table.td></x-table.td>
                                @endforeach
                            @else
                                @php
                                    $tenantScores = $sctenant->SCTenantHealthscore->getScores();
                                    $thresholds = $sctenant->healthscoreThresholdMap();
                                @endphp
                                @foreach ($scores as $score)
                                    @if($tenantScores[$score] >= 0)
                                        <x-table.td class="w-24 min-w-24">
                                            <div class="flex justify-center">
                                                <x-healthscore-badge :score="$tenantScores[$score]" :threshold="$thresholds[$score] ?? null" />
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