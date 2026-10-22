<?php

use App\Actions\EvaluateSCTenantHealthscoreThresholds;
use App\Models\SCTenant;
use App\Models\SCTenantHealthscore;
use App\Models\SCTenantHealthscoreThreshold;
use Livewire\Volt\Component;

new class extends Component {
    public SCTenant $sctenant;

    /**
     * A numerically indexed list rather than a map keyed by score.
     *
     * Score keys contain dots ("protection.computer.score"), which both wire:model and
     * the validator would read as nested array paths.
     *
     * @var array<int, array{key: string, threshold: int}>
     */
    public array $thresholds = [];

    public string $message = '';

    public function mount(SCTenant $sctenant): void
    {
        $this->sctenant = $sctenant;
        $this->loadThresholds();
    }

    private function loadThresholds(): void
    {
        $stored = SCTenantHealthscoreThreshold::where('tenantId', $this->sctenant->getKey())
            ->pluck('threshold', 'score_key');

        $this->thresholds = collect($this->scoreKeys())
            ->map(fn (string $key) => [
                'key' => $key,
                'threshold' => (int) ($stored[$key] ?? SCTenantHealthscoreThreshold::defaultThreshold()),
            ])->all();
    }

    /**
     * The available scores grow over time, so this is always read from the model rather
     * than from what happens to be stored.
     *
     * @return array<int, string>
     */
    private function scoreKeys(): array
    {
        return SCTenantHealthscore::AvailableScores();
    }

    public function with(): array
    {
        $healthscore = $this->sctenant->SCTenantHealthscore;
        $current = $healthscore?->hasScores() ? $healthscore->getScores() : collect();

        $rows = SCTenantHealthscoreThreshold::where('tenantId', $this->sctenant->getKey())
            ->get()
            ->keyBy('score_key');

        return [
            'scoreKeys' => $this->scoreKeys(),
            'currentScores' => $current,
            'states' => $rows,
            'defaultThreshold' => SCTenantHealthscoreThreshold::defaultThreshold(),
            // Rows for scores Sophos no longer reports are shown rather than hidden, so
            // stale configuration is visible and removable.
            'orphaned' => $rows->keys()->diff($this->scoreKeys()),
        ];
    }

    public function save(): void
    {
        $this->validate([
            'thresholds.*.threshold' => 'required|integer|min:0|max:100',
        ]);

        foreach ($this->thresholds as $row) {
            SCTenantHealthscoreThreshold::updateOrCreate(
                ['tenantId' => $this->sctenant->getKey(), 'score_key' => $row['key']],
                ['threshold' => (int) $row['threshold']],
            );
        }

        $this->message = __('Saved');
        $this->loadThresholds();
    }

    /**
     * Establish the baseline without raising events.
     *
     * Useful after changing several thresholds at once when the current state is already
     * known and should not be announced as a batch of transitions.
     */
    public function seedBaseline(EvaluateSCTenantHealthscoreThresholds $action): void
    {
        $healthscore = $this->sctenant->SCTenantHealthscore;

        if ($healthscore === null) {
            $this->message = __('No healthscore has been fetched for this tenant yet.');

            return;
        }

        $action->seedSilently($this->sctenant, $healthscore);
        $this->message = __('Baseline updated without raising events.');
    }

    public function deleteOrphan(string $scoreKey): void
    {
        SCTenantHealthscoreThreshold::where('tenantId', $this->sctenant->getKey())
            ->where('score_key', $scoreKey)
            ->delete();

        $this->message = __('Removed');
    }
}; ?>

<x-card title="{{ __('Healthscore Thresholds') }}" subtitle="{{ __('Raise an ISCPP event when a score drops below its threshold, and again when it recovers') }}">
    <flux:text size="sm" class="text-zinc-500">
        {{ __('Every score is monitored. A score with no explicit threshold uses the default of') }} {{ $defaultThreshold }}.
        {{ __('Which tenants actually receive a webhook is decided by the event rule, not here.') }}
    </flux:text>

    <div class="relative overflow-x-auto">
        <x-table.table>
            <x-table.thead>
                <tr>
                    <x-table.th compact>{{ __('Score') }}</x-table.th>
                    <x-table.th compact class="w-24">{{ __('Current') }}</x-table.th>
                    <x-table.th compact class="w-28">{{ __('State') }}</x-table.th>
                    <x-table.th compact class="w-32">{{ __('Threshold') }}</x-table.th>
                </tr>
            </x-table.thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach($thresholds as $index => $row)
                    @php($scoreKey = $row['key'])
                    @php($value = $currentScores[$scoreKey] ?? -1)
                    <x-table.tr>
                        <x-table.td compact>
                            <span class="font-medium capitalize">{{ Str::replace('.', ' ', $scoreKey) }}</span>
                            <div class="text-xs text-zinc-400 dark:text-zinc-500">{{ $scoreKey }}</div>
                        </x-table.td>
                        <x-table.td compact>
                            <x-healthscore-badge :score="$value" />
                        </x-table.td>
                        <x-table.td compact>
                            @php($state = $states[$scoreKey] ?? null)
                            @if($state)
                                <flux:badge color="{{ $state->stateBadgeColor() }}" size="sm">{{ $state->state }}</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">{{ __('unknown') }}</flux:badge>
                            @endif
                        </x-table.td>
                        <x-table.td compact>
                            <flux:input type="number" min="0" max="100" size="sm" wire:model="thresholds.{{ $index }}.threshold" />
                        </x-table.td>
                    </x-table.tr>
                @endforeach

                @foreach($orphaned as $scoreKey)
                    <x-table.tr>
                        <x-table.td compact>
                            <span class="font-medium">{{ $scoreKey }}</span>
                            <flux:badge color="amber" size="sm">{{ __('no longer reported') }}</flux:badge>
                        </x-table.td>
                        <x-table.td compact colspan="2"></x-table.td>
                        <x-table.td compact>
                            <flux:button size="xs" variant="danger" wire:click="deleteOrphan('{{ $scoreKey }}')">{{ __('Remove') }}</flux:button>
                        </x-table.td>
                    </x-table.tr>
                @endforeach
            </tbody>
        </x-table.table>
    </div>

    <div class="mt-4 flex items-center justify-end gap-2">
        <flux:button variant="ghost" wire:click="seedBaseline">{{ __('Set baseline silently') }}</flux:button>
        <x-a-button wire:click="save">{{ __('Save') }}</x-a-button>
    </div>

    @if($message !== '')
        <div class="py-2">{{ $message }}</div>
    @endif
</x-card>
