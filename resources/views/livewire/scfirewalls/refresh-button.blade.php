<?php

use App\Jobs\RefreshSCFirewalls;
use App\Models\SCFirewall;
use Livewire\Volt\Component;

new class extends Component {
    public SCFirewall $firewall;

    public string $message = '';

    public function mount(SCFirewall $firewall): void
    {
        $this->firewall = $firewall;
    }

    /**
     * The Firewall API has no request for a single firewall, so this refreshes every
     * firewall of the tenant. Tenants rarely have more than a handful.
     */
    public function refresh(): void
    {
        $tenant = $this->firewall->SCTenant;

        if ($tenant === null || $tenant->iscpp_ignore) {
            $this->message = __('The tenant of this firewall is missing or ignored by ISCPP and is not refreshed.');

            return;
        }

        RefreshSCFirewalls::dispatch($tenant);

        $this->message = __('Firewall refresh queued for all firewalls of this tenant.');
    }
}; ?>

<div class="flex flex-col items-end gap-1">
    <flux:button size="sm" icon="arrow-path" wire:click="refresh">{{ __('Refresh') }}</flux:button>
    @if($message !== '')
        <flux:text size="sm" class="text-zinc-500">{{ $message }}</flux:text>
    @endif
</div>
