<?php

namespace App\Events;

use App\Models\SCAlert;
use App\Models\SCTenant;
use Illuminate\Database\Eloquent\Model;

class ISCPPSCAlertCreated extends ISCPPEventBase
{
    public function __construct(public SCAlert $scalert) {}

    public static function key(): string
    {
        return 'sc.alert.created';
    }

    public static function label(): string
    {
        return __('SC Alert Created');
    }

    public static function defaultSeverity(): ISCPPEventSeverity
    {
        return ISCPPEventSeverity::Warning;
    }

    public function data(): array
    {
        return [
            'alertId' => $this->scalert->id,
            'severity' => $this->scalert->severity,
            'category' => $this->scalert->category,
            'type' => $this->scalert->type,
            'product' => $this->scalert->product,
            'description' => $this->scalert->description,
            'managedAgentName' => $this->scalert->managedAgentName,
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
        return [
            'data.severity' => 'Alert Severity (high/medium/low)',
            'data.category' => 'Category',
            'data.type' => 'Type',
            'data.product' => 'Product',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function exampleData(): array
    {
        return ['alertId' => '00000000-0000-0000-0000-000000000000', 'severity' => 'high', 'category' => 'malware', 'type' => 'Event::Endpoint::Threat::Detected', 'product' => 'endpoint', 'description' => 'Malware detected', 'managedAgentName' => 'WS-EXAMPLE-01', 'raisedAt' => '2026-09-11T08:00:00+00:00'];
    }

    public static function description(): string
    {
        return __('Raised when a new Sophos Central alert is stored. Importing the alert history of a tenant is not announced.');
    }
}
