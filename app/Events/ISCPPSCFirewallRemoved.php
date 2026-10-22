<?php

namespace App\Events;

use App\Models\SCFirewall;
use App\Models\SCTenant;

class ISCPPSCFirewallRemoved extends ISCPPEventBase
{
    public function __construct(public SCFirewall $scfirewall) {}

    public static function key(): string
    {
        return 'sc.firewall.removed';
    }

    public static function label(): string
    {
        return __('SC Firewall Removed');
    }

    public static function defaultSeverity(): ISCPPEventSeverity
    {
        return ISCPPEventSeverity::Warning;
    }

    public static function description(): string
    {
        return __('Raised when a firewall is deleted from ISCPP. Nothing deletes firewalls automatically yet, so this event is currently dormant.');
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

    public static function exampleData(): array
    {
        return [
            'firewallId' => '00000000-0000-0000-0000-000000000000',
            'name' => 'FW-EXAMPLE',
            'hostname' => 'fw-example.local',
        ];
    }
}
