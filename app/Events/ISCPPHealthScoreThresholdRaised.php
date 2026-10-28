<?php

namespace App\Events;

use App\Models\SCTenant;
use App\Models\SCTenantHealthscoreThreshold;
use Illuminate\Database\Eloquent\Model;

class ISCPPHealthScoreThresholdRaised extends ISCPPEventBase
{
    public function __construct(
        public SCTenant $sctenant,
        public SCTenantHealthscoreThreshold $threshold,
        public int $value,
        public int $previousThreshold,
    ) {}

    public static function key(): string
    {
        return 'healthscore.threshold.raised';
    }

    public static function label(): string
    {
        return __('Healthscore Threshold Raised');
    }

    public function data(): array
    {
        return [
            'scoreKey' => $this->threshold->score_key,
            'scoreLabel' => $this->threshold->scoreLabel(),
            'value' => $this->value,
            'threshold' => $this->threshold->threshold,
            'previousThreshold' => $this->previousThreshold,
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
            'data.previousThreshold' => 'Previous Threshold',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function exampleData(): array
    {
        return ['scoreKey' => 'protection.computer.score', 'scoreLabel' => 'Protection Computer Score', 'value' => 90, 'threshold' => 90, 'previousThreshold' => 80];
    }

    public static function description(): string
    {
        return __('Raised when a healthscore rises above its threshold. The threshold follows the score up automatically, so a later drop below the new value raises Healthscore Threshold Dropped.');
    }
}
