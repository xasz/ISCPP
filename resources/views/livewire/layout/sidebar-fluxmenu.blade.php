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
        <flux:navlist.group heading="Central" class="grid">
            <flux:navlist.item icon="building-office" :href="route('sctenants.index')" :current="request()->routeIs('sctenants.index')" wire:navigate>SC Tenants</flux:navlist.item>
            @if(resolve(App\Settings\SCServiceSettings::class)->alertsScheduleEnabled)
            <flux:navlist.item icon="bell-alert" :href="route('scalerts.index')" :current="request()->routeIs('scalerts.index')" wire:navigate>SC Alerts</flux:navlist.item>
            @endif
            @if(resolve(App\Settings\SCServiceSettings::class)->endpointsScheduleEnabled)
            <flux:navlist.item icon="computer-desktop" :href="route('scendpoints.index')" :current="request()->routeIs('scendpoints.index')" wire:navigate>SC Endpoints</flux:navlist.item>
            @endif
            @if(resolve(App\Settings\SCServiceSettings::class)->firewallsScheduleEnabled)
            <flux:navlist.item icon="fire" :href="route('scfirewalls.index')" :current="request()->routeIs('scfirewalls.index')" wire:navigate>SC Firewalls (soon)</flux:navlist.item>
            @endif
        </flux:navlist.group>

        <flux:navlist.group heading="Tools" class="grid">
            @if(resolve(App\Settings\SCServiceSettings::class)->healthscoresScheduleEnabled)
            <flux:navlist.item icon="heart" :href="route('sctenants.healthscores')" :current="request()->routeIs('sctenants.healthscores')" wire:navigate>Tenant Healthscores</flux:navlist.item>
            @endif
        </flux:navlist.group>

        <flux:navlist.group heading="Central Billing" class="grid">
            <flux:navlist.item icon="arrow-down-on-square" :href="route('scbilling.fetcher')" :current="request()->routeIs('scbilling.fetcher')" wire:navigate>Fetcher</flux:navlist.item>
            <flux:navlist.item icon="document-check" :href="route('scbillables.index')" :current="request()->routeIs('scbilling.index')" wire:navigate>Viewer</flux:navlist.item>
            @if(resolve(App\Settings\HaloServiceSettings::class)->enabled)
                <flux:navlist.item icon="banknotes" :href="route('scbilling.haloPusher')" :current="request()->routeIs('scbilling.haloPusher')" wire:navigate>Halo Pusher</flux:navlist.item>
                <flux:navlist.item icon="ellipsis-horizontal" :href="route('scbilling.haloSettings')" :current="request()->routeIs('scbilling.haloSettings')" wire:navigate>Halo Settings</flux:navlist.item>
            @endif
        </flux:navlist.group>    
        <flux:navlist.group heading="Events" class="grid">
            <flux:navlist.item icon="cake" :href="route('events.index')" :current="request()->routeIs('events.index')" wire:navigate>System</flux:navlist.item>
            <flux:navlist.item icon="bolt" :href="route('webhookLogs.index')" :current="request()->routeIs('webhookLogs.index')" wire:navigate>SC Alert Webhooks</flux:navlist.item>
        </flux:navlist.group>  
    </flux:navlist>
    
</div>