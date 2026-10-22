<?php

namespace App\Events;

use App\Models\SCTenant;
use App\Models\SCTenantHealthscoreThreshold;
use Illuminate\Database\Eloquent\Model;

class ISCPPHealthScoreThresholdDropped extends ISCPPEventBase
{
    public function __construct(
        public SCTenant $sctenant,
        public SCTenantHealthscoreThreshold $threshold,
        public int $value,
        public ?int $previousValue = null,
    ) {}

    public static function key(): string
    {
        return 'healthscore.threshold.dropped';
    }

    public static function label(): string
    {
        return __('Healthscore Threshold Dropped');
    }

    public static function defaultSeverity(): ISCPPEventSeverity
    {
        return ISCPPEventSeverity::Warning;
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
        return ['scoreKey' => 'protection.computer.score', 'scoreLabel' => 'Protection Computer Score', 'value' => 41, 'threshold' => 100, 'previousValue' => 100];
    }

    public static function description(): string
    {
        return __('Raised when a healthscore drops below the tenant threshold, either because the score fell or the threshold was raised.');
    }
}
