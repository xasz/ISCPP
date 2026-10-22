<?php

namespace App\Events;

use App\Models\SCTenant;
use Illuminate\Database\Eloquent\Model;

class ISCPPSCTenantCreated extends ISCPPEventBase
{
    public function __construct(public SCTenant $sctenant) {}

    public static function key(): string
    {
        return 'sc.tenant.created';
    }

    public static function label(): string
    {
        return __('SC Tenant Created');
    }

    public function data(): array
    {
        return [
            'name' => $this->sctenant->name,
            'showAs' => $this->sctenant->showAs,
            'billingType' => $this->sctenant->billingType,
            'dataGeography' => $this->sctenant->dataGeography,
        ];
    }

    public function tenant(): ?SCTenant
    {
        return $this->sctenant;
    }

    public function subject(): ?Model
    {
        return $this->sctenant;
    }

    public static function filterablePaths(): array
    {
        return ['data.billingType' => 'Billing Type', 'data.dataGeography' => 'Data Geography'];
    }

    /**
     * @return array<string, mixed>
     */
    public static function exampleData(): array
    {
        return ['name' => 'Example Tenant GmbH', 'showAs' => 'Example Tenant GmbH - 12345', 'billingType' => 'usage', 'dataGeography' => 'DE'];
    }

    public static function description(): string
    {
        return __('Raised when a tenant appears in Sophos Central for the first time. The very first tenant import is not announced.');
    }
}
