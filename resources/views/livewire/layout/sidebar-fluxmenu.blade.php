<?php

use Illuminate\Support\Facades\Http;
use Livewire\Volt\Component;
use Livewire\Attributes\On; 


new class extends Component {
    #[On('featureSet-changed')] 
    public function justUpdate()
    {
    }    
}; ?>

<div class="overflow-visible min-h-auto flex flex-col">
    <flux:navlist variant="outline">
        <flux:navlist.group heading="Platform" class="grid">
            <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>Dashboard</flux:navlist.item>
        </flux:navlist.group>
        @php
            $scSettings = resolve(App\Settings\SCServiceSettings::class);
            $haloEnabled = resolve(App\Settings\HaloServiceSettings::class)->enabled;
            $ninjaEnabled = resolve(App\Settings\NinjaServiceSettings::class)->enabled;
        @endphp

        <flux:navlist.group heading="Central Tenants" class="grid">
            <flux:navlist.item icon="building-office" :href="route('sctenants.index')" :current="request()->routeIs('sctenants.index')" wire:navigate>Tenants</flux:navlist.item>
            <flux:navlist.item icon="user-group" :href="route('sctenantgroups.index')" :current="request()->routeIs('sctenantgroups.*')" wire:navigate>Groups</flux:navlist.item>
            @if($scSettings->healthscoresScheduleEnabled)
            <flux:navlist.item icon="heart" :href="route('sctenants.healthscores')" :current="request()->routeIs('sctenants.healthscores')" wire:navigate>Healthscores</flux:navlist.item>
            @endif
        </flux:navlist.group>

        @if($scSettings->alertsScheduleEnabled)
        <flux:navlist.group heading="Central Alerts" class="grid">
            <flux:navlist.item icon="bell-alert" :href="route('scalerts.index')" :current="request()->routeIs('scalerts.index')" wire:navigate>Alerts</flux:navlist.item>
            <flux:navlist.item icon="adjustments-horizontal" :href="route('scalerts.autoActions')" :current="request()->routeIs('scalerts.autoActions')" wire:navigate>AutoActions</flux:navlist.item>
        </flux:navlist.group>
        @endif

        @if($scSettings->firewallsScheduleEnabled)
        <flux:navlist.group heading="Central Firewalls" class="grid">
            <flux:navlist.item icon="fire" :href="route('scfirewalls.index')" :current="request()->routeIs('scfirewalls.index')" wire:navigate>Firewalls</flux:navlist.item>
        </flux:navlist.group>
        @endif

        @if($scSettings->endpointsScheduleEnabled)
        <flux:navlist.group heading="Central Endpoints" class="grid">
            <flux:navlist.item icon="computer-desktop" :href="route('scendpoints.index')" :current="request()->routeIs('scendpoints.index')" wire:navigate>Endpoints</flux:navlist.item>
        </flux:navlist.group>
        @endif

        @if($haloEnabled || $ninjaEnabled)
        <flux:navlist.group heading="Tools" class="grid">
            @if($haloEnabled)
                <flux:navlist.item icon="ellipsis-horizontal" :href="route('sctenants.haloMatchingHelper')" :current="request()->routeIs('sctenants.haloMatchingHelper')" wire:navigate>Halo Matchhelper</flux:navlist.item>
            @endif
            @if($ninjaEnabled)
                <flux:navlist.item icon="ellipsis-horizontal" :href="route('sctenants.ninjaMatchingHelper')" :current="request()->routeIs('sctenants.ninjaMatchingHelper')" wire:navigate>Ninja Matchhelper</flux:navlist.item>
            @endif
        </flux:navlist.group>
        @endif

        <flux:navlist.group heading="Central Billing" class="grid">
            <flux:navlist.item icon="arrow-down-on-square" :href="route('scbilling.fetcher')" :current="request()->routeIs('scbilling.fetcher')" wire:navigate>Fetcher</flux:navlist.item>
            <flux:navlist.item icon="document-check" :href="route('scbillables.index')" :current="request()->routeIs('scbilling.index')" wire:navigate>Viewer</flux:navlist.item>
            @if(resolve(App\Settings\HaloServiceSettings::class)->enabled)
                <flux:navlist.item icon="banknotes" :href="route('scbilling.haloPusher')" :current="request()->routeIs('scbilling.haloPusher')" wire:navigate>Halo Pusher</flux:navlist.item>
            @endif
        </flux:navlist.group>    
        <flux:navlist.group heading="Events" class="grid">
            <flux:navlist.item icon="rectangle-stack" :href="route('iscpp.events.index')" :current="request()->routeIs('iscpp.events.*')" wire:navigate>Event Log</flux:navlist.item>
            <flux:navlist.item icon="funnel" :href="route('iscpp.rules.index')" :current="request()->routeIs('iscpp.rules.*')" wire:navigate>Rules</flux:navlist.item>
            <flux:navlist.item icon="information-circle" :href="route('iscpp.info')" :current="request()->routeIs('iscpp.info')" wire:navigate>Event Info</flux:navlist.item>
        </flux:navlist.group>
        <flux:navlist.group heading="Logs" class="grid">
            <flux:navlist.item icon="document-text" :href="route('events.index')" :current="request()->routeIs('events.index')" wire:navigate>System Log</flux:navlist.item>
            <flux:navlist.item icon="bolt" :href="route('webhookLogs.index')" :current="request()->routeIs('webhookLogs.index')" wire:navigate>SC Alert Webhooks (Legacy)</flux:navlist.item>
        </flux:navlist.group>
    </flux:navlist>
    
</div>