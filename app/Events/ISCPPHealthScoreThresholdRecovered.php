<?php

namespace App\Events;

use App\Models\SCTenant;
use App\Models\SCTenantHealthscoreThreshold;
use Illuminate\Database\Eloquent\Model;

class ISCPPHealthScoreThresholdRecovered extends ISCPPEventBase
{
    public function __construct(
        public SCTenant $sctenant,
        public SCTenantHealthscoreThreshold $threshold,
        public int $value,
        public ?int $previousValue = null,
    ) {}

    public static function key(): string
    {
        return 'healthscore.threshold.recovered';
    }

    public static function label(): string
    {
        return __('Healthscore Threshold Recovered');
    }

    public function data(): array
    {
        return [
            'scoreKey' => $this->threshold->score_key,
            'scoreLabel' => $this->threshold->scoreLabel(),
            'value' => $this->value,
            'threshold' => $this->threshold->threshold,
            'previousValue' => $this->previousValue,
        ];
    }

    public function tenant(): ?SCTenant
    {
        return $this->sctenant;
    }

    public function subject(): ?Model
    {
        return $this->threshold;
    }

    public static function filterablePaths(): array
    {
        return [
            'data.scoreKey' => 'Score Key',
            'data.value' => 'Current Value',
            'data.threshold' => 'Threshold',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function exampleData(): array
    {
        return ['scoreKey' => 'protection.computer.score', 'scoreLabel' => 'Protection Computer Score', 'value' => 100, 'threshold' => 100, 'previousValue' => 41];
    }

    public static function description(): string
    {
        return __('Raised when a healthscore that was below its threshold is at or above it again, either because the score rose or the threshold was lowered.');
    }
}
