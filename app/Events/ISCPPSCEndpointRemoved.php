<?php

namespace App\Events;

use App\Models\SCEndpoint;
use App\Models\SCTenant;

class ISCPPSCEndpointRemoved extends ISCPPEventBase
{
    public function __construct(public SCEndpoint $scendpoint) {}

    public static function key(): string
    {
        return 'sc.endpoint.removed';
    }

    public static function label(): string
    {
        return __('SC Endpoint Removed');
    }

    public static function defaultSeverity(): ISCPPEventSeverity
    {
        return ISCPPEventSeverity::Warning;
    }

    public function data(): array
    {
        return [
            'hostname' => $this->scendpoint->hostname,
            'type' => $this->scendpoint->type,
        ];
    }

    public function tenant(): ?SCTenant
    {
        return $this->scendpoint->SCTenant;
    }

    /**
     * @return array<string, mixed>
     */
    public static function exampleData(): array
    {
        return ['hostname' => 'WS-EXAMPLE-01', 'type' => 'computer'];
    }

    public static function description(): string
    {
        return __('Raised when an endpoint is deleted. Nothing deletes endpoints automatically yet, so this event is currently dormant.');
    }
}
