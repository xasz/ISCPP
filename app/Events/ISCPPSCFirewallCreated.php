<?php

namespace App\Events;

use App\Models\SCFirewall;
use App\Models\SCTenant;
use Illuminate\Database\Eloquent\Model;

class ISCPPSCFirewallCreated extends ISCPPEventBase
{
    public function __construct(public SCFirewall $scfirewall) {}

    public static function key(): string
    {
        return 'sc.firewall.created';
    }

    public static function label(): string
    {
        return __('SC Firewall Created');
    }

    public static function description(): string
    {
        return __('Raised when a new firewall is synced for a tenant. The first firewall import of a tenant is not announced.');
    }

    public function data(): array
    {
        return [
            'firewallId' => $this->scfirewall->id,
            // sc_firewalls has no name column; Sophos keeps it in the raw payload.
            'name' => $this->scfirewall->rawData['name'] ?? null,
            'hostname' => $this->scfirewall->hostname,
        ];
    }

    public function tenant(): ?SCTenant
    {
        return $this->scfirewall->SCTenant;
    }

    public function subject(): ?Model
    {
        return $this->scfirewall;
    }

    public static function exampleData(): array
    {
        return [
            'firewallId' => '00000000-0000-0000-0000-000000000000',
            'name' => 'FW-EXAMPLE',
            'hostname' => 'fw-example.local',
        ];
    }
}
