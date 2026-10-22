<?php

namespace App\Events;

use App\Models\SCTenant;

class ISCPPSCTenantRemoved extends ISCPPEventBase
{
    public function __construct(public SCTenant $sctenant) {}

    public static function key(): string
    {
        return 'sc.tenant.removed';
    }

    public static function label(): string
    {
        return __('SC Tenant Removed');
    }

    public static function defaultSeverity(): ISCPPEventSeverity
    {
        return ISCPPEventSeverity::Warning;
    }

    public function data(): array
    {
        return [
            'name' => $this->sctenant->name,
            'showAs' => $this->sctenant->showAs,
            'billingType' => $this->sctenant->billingType,
        ];
    }

    public function tenant(): ?SCTenant
    {
        return $this->sctenant;
    }

    /**
     * @return array<string, mixed>
     */
    public static function exampleData(): array
    {
        return ['name' => 'Example Tenant GmbH', 'showAs' => 'Example Tenant GmbH - 12345', 'billingType' => 'usage'];
    }

    public static function description(): string
    {
        return __('Raised when a tenant has been missing from Sophos Central for longer than the grace period and is deleted.');
    }
}
