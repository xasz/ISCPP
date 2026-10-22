<?php

namespace App\Events;

use App\Models\SCTenant;
use App\Models\SCTenantHealthscore;
use Illuminate\Database\Eloquent\Model;

class ISCPPHealthScoreCreated extends ISCPPEventBase
{
    public function __construct(public SCTenantHealthscore $healthscore) {}

    public static function key(): string
    {
        return 'healthscore.created';
    }

    public static function label(): string
    {
        return __('Healthscore Created');
    }

    public static function description(): string
    {
        return __('Raised when healthscores are stored for a tenant for the first time. Later refreshes update them and do not raise this event.');
    }

    public function data(): array
    {
        return [
            // Missing scores are reported as null rather than -1.
            'scores' => $this->healthscore->getScores()
                ->map(fn (mixed $value) => is_int($value) && $value >= 0 ? $value : null)
                ->all(),
        ];
    }

    public function tenant(): ?SCTenant
    {
        return $this->healthscore->SCTenant;
    }

    public function subject(): ?Model
    {
        return $this->healthscore;
    }

    public static function exampleData(): array
    {
        return [
            'scores' => [
                'protection.computer.score' => 100,
                'protection.server.score' => 87,
                'mdrAuthorizedContact.contact.score' => null,
            ],
        ];
    }
}
