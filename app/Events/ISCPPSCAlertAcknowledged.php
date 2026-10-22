<?php

namespace App\Events;

use App\Models\SCAlert;
use App\Models\SCTenant;
use Illuminate\Database\Eloquent\Model;

class ISCPPSCAlertAcknowledged extends ISCPPEventBase
{
    public function __construct(public SCAlert $scalert) {}

    public static function key(): string
    {
        return 'sc.alert.acknowledged';
    }

    public static function label(): string
    {
        return __('SC Alert Acknowledged');
    }

    public static function description(): string
    {
        return __('Raised when ISCPP acknowledges an alert in Sophos Central, manually or through an auto action. Acknowledgements made directly in Sophos Central are not synced and do not raise it.');
    }

    public function data(): array
    {
        return [
            'alertId' => $this->scalert->id,
            'severity' => $this->scalert->severity,
            'category' => $this->scalert->category,
            'type' => $this->scalert->type,
            'description' => $this->scalert->description,
            'raisedAt' => $this->scalert->raisedAt?->toIso8601String(),
        ];
    }

    public function tenant(): ?SCTenant
    {
        return $this->scalert->SCTenant;
    }

    public function subject(): ?Model
    {
        return $this->scalert;
    }

    public static function filterablePaths(): array
    {
        return ['data.severity' => 'Alert Severity (high/medium/low)', 'data.type' => 'Type'];
    }

    public static function exampleData(): array
    {
        return [
            'alertId' => '00000000-0000-0000-0000-000000000000',
            'severity' => 'high',
            'category' => 'malware',
            'type' => 'Event::Endpoint::Threat::Detected',
            'description' => 'Malware detected',
            'raisedAt' => '2026-09-11T08:00:00+00:00',
        ];
    }
}
