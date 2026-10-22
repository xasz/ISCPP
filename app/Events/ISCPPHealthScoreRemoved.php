<?php

namespace App\Events;

use App\Models\SCTenant;
use App\Models\SCTenantHealthscore;

class ISCPPHealthScoreRemoved extends ISCPPEventBase
{
    public function __construct(public SCTenantHealthscore $healthscore) {}

    public static function key(): string
    {
        return 'healthscore.removed';
    }

    public static function label(): string
    {
        return __('Healthscore Removed');
    }

    public static function defaultSeverity(): ISCPPEventSeverity
    {
        return ISCPPEventSeverity::Warning;
    }

    public static function description(): string
    {
        return __('Raised when the healthscores of a tenant are deleted. Nothing deletes healthscores automatically yet, so this event is currently dormant.');
    }

    public function data(): array
    {
        return [];
    }

    public function tenant(): ?SCTenant
    {
        return $this->healthscore->SCTenant;
    }
}
