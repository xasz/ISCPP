<x-sctenants.show.layout :sctenant="$sctenant">
    <x-card title="Healthscore"> 
        @if($sctenant->SCTenantHealthscore()->count() > 0)
            <div class="grid grid-cols-1 gap-4">
                @if($sctenant->SCTenantHealthscore->hasScores())
                    @foreach($sctenant->SCTenantHealthscore->getScores() as $key => $score)
                        @if($score >= 0)
                            <div class="flex items-center gap-2">
                                <x-healthscore-badge :score="$score" size="lg" />
                                <flux:text size="lg" class="capitalize">{{ Str::replace('.', ' ', $key) }}</flux:text>
                            </div>
                        @endif
                    @endforeach
                @else
                    <flux:text>
                        {{ __('Healthscore is not available for this tenant.') }}
                    </flux:text>
                @endif
            </div>
            <x-card-details-json :arr="$sctenant->SCTenantHealthscore->rawData" />
        @else
            <flux:text>
                {{ __('No Healthscore available for this tenant.') }}
            </flux:text>
        @endif
    </x-card>
</x-sctenants.show.layout>