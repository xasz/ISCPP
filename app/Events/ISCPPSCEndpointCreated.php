<?php

namespace App\Events;

use App\Models\SCEndpoint;
use App\Models\SCTenant;
use Illuminate\Database\Eloquent\Model;

class ISCPPSCEndpointCreated extends ISCPPEventBase
{
    public function __construct(public SCEndpoint $scendpoint) {}

    public static function key(): string
    {
        return 'sc.endpoint.created';
    }

    public static function label(): string
    {
        return __('SC Endpoint Created');
    }

    public function data(): array
    {
        return [
            'hostname' => $this->scendpoint->hostname,
            'type' => $this->scendpoint->type,
            'healthStatus' => $this->scendpoint->healthStatus,
            'tamperProtectionEnabled' => $this->scendpoint->tamperProtectionEnabled,
        ];
    }

    public function tenant(): ?SCTenant
    {
        return $this->scendpoint->SCTenant;
    }

    public function subject(): ?Model
    {
        return $this->scendpoint;
    }

    public static function filterablePaths(): array
    {
        return ['data.healthStatus' => 'Health Status', 'data.type' => 'Type'];
    }

    /**
     * @return array<string, mixed>
     */
    public static function exampleData(): array
    {
        return ['hostname' => 'WS-EXAMPLE-01', 'type' => 'computer', 'healthStatus' => 'good', 'tamperProtectionEnabled' => true];
    }

    public static function description(): string
    {
        return __('Raised when a new endpoint is synced for a tenant. The first endpoint import of a tenant is not announced.');
    }
}
