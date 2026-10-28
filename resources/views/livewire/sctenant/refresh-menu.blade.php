<?php

use App\Jobs\RefreshSCAlerts;
use App\Jobs\RefreshSCEndpoints;
use App\Jobs\RefreshSCFirewalls;
use App\Jobs\RefreshSCTenantDownload;
use App\Jobs\RefreshSCTenantHealthscore;
use App\Models\Event;
use App\Models\SCTenant;
use App\Settings\SCServiceSettings;
use Livewire\Volt\Component;

new class extends Component {
    public SCTenant $sctenant;

    public string $message = '';

    public function mount(SCTenant $sctenant): void
    {
        $this->sctenant = $sctenant;
    }

    /**
     * Only areas whose feature is enabled are offered, matching the scheduler.
     *
     * @return array<string, string>
     */
    private function availableAreas(): array
    {
        $settings = resolve(SCServiceSettings::class);

        return array_filter([
            'alerts' => $settings->alertsScheduleEnabled ? __('Alerts') : null,
            'endpoints' => $settings->endpointsScheduleEnabled ? __('Endpoints') : null,
            'firewalls' => $settings->firewallsScheduleEnabled ? __('Firewalls') : null,
            'healthscore' => $settings->healthscoresScheduleEnabled ? __('Healthscore') : null,
            'downloads' => $settings->downloadsScheduleEnabled ? __('Downloads') : null,
        ]);
    }

    public function with(): array
    {
        return ['areas' => $this->availableAreas()];
    }

    public function refresh(string $area): void
    {
        $areas = $this->availableAreas();

        if (! array_key_exists($area, $areas)) {
            return;
        }

        if ($this->sctenant->iscpp_ignore) {
            $this->message = __('This tenant is ignored by ISCPP and is not refreshed.');

            return;
        }

        match ($area) {
            'alerts' => RefreshSCAlerts::dispatch($this->sctenant),
            'endpoints' => RefreshSCEndpoints::dispatch($this->sctenant),
            'firewalls' => RefreshSCFirewalls::dispatch($this->sctenant),
            'healthscore' => RefreshSCTenantHealthscore::dispatch($this->sctenant),
            'downloads' => RefreshSCTenantDownload::dispatch($this->sctenant),
        };

        Event::logInfo('sctenants', 'Manual refresh queued', ['SCTenant' => $this->sctenant->id, 'area' => $area]);

        $this->message = __(':area refresh queued.', ['area' => $areas[$area]]);
    }
}; ?>

<div class="flex flex-col items-end gap-1">
    <flux:dropdown position="bottom" align="end">
        <flux:button size="sm" icon="arrow-path" icon:trailing="chevron-down">{{ __('Refresh') }}</flux:button>
        <flux:menu>
            @foreach($areas as $area => $label)
                <flux:menu.item wire:click="refresh('{{ $area }}')">{{ $label }}</flux:menu.item>
            @endforeach
        </flux:menu>
    </flux:dropdown>
    @if($message !== '')
        <flux:text size="sm" class="text-zinc-500">{{ $message }}</flux:text>
    @endif
</div>
