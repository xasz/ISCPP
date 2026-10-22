<?php

namespace App\Events;

use App\Models\SCAlert;
use App\Models\SCTenant;

class ISCPPSCAlertRemoved extends ISCPPEventBase
{
    public function __construct(public SCAlert $scalert) {}

    public static function key(): string
    {
        return 'sc.alert.removed';
    }

    public static function label(): string
    {
        return __('SC Alert Removed');
    }

    public static function description(): string
    {
        return __('Raised when an alert is deleted from ISCPP. Nothing deletes alerts automatically yet, so this event is currently dormant.');
    }

    public function data(): array
    {
        return [
            'alertId' => $this->scalert->id,
            'severity' => $this->scalert->severity,
            'type' => $this->scalert->type,
            'description' => $this->scalert->description,
        ];
    }

    public function tenant(): ?SCTenant
    {
        return $this->scalert->SCTenant;
    }

    public static function exampleData(): array
    {
        return [
            'alertId' => '00000000-0000-0000-0000-000000000000',
            'severity' => 'high',
            'type' => 'Event::Endpoint::Threat::Detected',
            'description' => 'Malware detected',
        ];
    }
}
