<?php

use App\Jobs\RefreshSCAlerts;
use App\Models\SCTenant;
use App\Services\SCService;
use Livewire\Volt\Component;
use App\Jobs\RefreshSCTenants;
use App\Models\SCAlert;
use App\Models\SCBillable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

new class extends Component {
    /**
     * Runs on every request of this component, not only on mount.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-settings');
    }


    public function alerts(SCService $scService)
    {
        Artisan::call('app:queue-refresh-scalerts-jobs-for-all-tenants');
    }

    
    public function allAlerts(SCService $scService)
    {
        Artisan::call('app:queue-refresh-all-scalerts-jobs-for-all-tenants');
    }

    public function endpoints(SCService $scService)
    {
        Artisan::call('app:queue-refresh-scendpoints-jobs-for-all-tenants');
    }

    
    public function downloads(SCService $scService)
    {
        Artisan::call('app:queue-refresh-downloads-jobs-for-all-tenants');
    }
    
    public function healthscore(SCService $scService)
    {
        Artisan::call('app:queue-refresh-healthscores-jobs-for-all-tenants');
    }

    public function removeBilling(){
        SCBillable::truncate();
    }
    
    public function purgeAlertData(){
        SCAlert::truncate();
    }

    public function runTenantRefresh(SCService $scService)
    {
        Artisan::call('app:queue-sctenants-refresh');
    }
    
    public function firewalls(SCService $scService)
    {
        Artisan::call('app:queue-refresh-scfirewalls-jobs-for-all-tenants');
    }

    public function dumpQueue()
    {
        dump(DB::table('jobs')->get());
    }

}; ?>
@php
    $refreshCommands = [
        ['action' => 'runTenantRefresh', 'label' => 'Tenants', 'description' => 'Queue a refresh of all tenants.'],
        ['action' => 'alerts', 'label' => 'Alerts (delta)', 'description' => 'Queue a refresh of new alerts for all tenants.'],
        ['action' => 'allAlerts', 'label' => 'Alerts (all)', 'description' => 'Queue a full refresh of all alerts for all tenants.'],
        ['action' => 'endpoints', 'label' => 'Endpoints', 'description' => 'Queue a refresh of endpoints for all tenants.'],
        ['action' => 'firewalls', 'label' => 'Firewalls', 'description' => 'Queue a refresh of firewalls for all tenants.'],
        ['action' => 'downloads', 'label' => 'Downloads', 'description' => 'Queue a refresh of downloads for all tenants.'],
        ['action' => 'healthscore', 'label' => 'Healthscores', 'description' => 'Queue a refresh of healthscores for all tenants.'],
    ];
@endphp
<x-card title="Running Commands" subtitle="This is for development">
    <flux:callout variant="warning" icon="exclamation-triangle" heading="When you are here - You really should know what you are doing" />

    <flux:heading size="sm" class="mt-6">Refresh jobs</flux:heading>
    <div class="mt-2 divide-y divide-zinc-100 dark:divide-zinc-800">
        @foreach ($refreshCommands as $command)
            <div class="flex items-center justify-between gap-4 py-2">
                <div>
                    <flux:text class="font-medium text-zinc-800 dark:text-zinc-200">{{ $command['label'] }}</flux:text>
                    <flux:text size="sm">{{ $command['description'] }}</flux:text>
                </div>
                <flux:button size="sm" icon="arrow-path" wire:click="{{ $command['action'] }}">Run</flux:button>
            </div>
        @endforeach
    </div>

    <flux:heading size="sm" class="mt-6">Debug</flux:heading>
    <div class="mt-2 flex items-center justify-between gap-4 py-2">
        <div>
            <flux:text class="font-medium text-zinc-800 dark:text-zinc-200">Dump queue</flux:text>
            <flux:text size="sm">Dump all jobs currently waiting in the queue.</flux:text>
        </div>
        <flux:button size="sm" icon="bug-ant" wire:click="dumpQueue">Dump</flux:button>
    </div>

    <flux:heading size="sm" class="mt-6 text-red-600 dark:text-red-400">Danger zone</flux:heading>
    <div class="mt-2 divide-y divide-zinc-100 dark:divide-zinc-800">
        <div class="flex items-center justify-between gap-4 py-2">
            <div>
                <flux:text class="font-medium text-zinc-800 dark:text-zinc-200">Remove billing data</flux:text>
                <flux:text size="sm">Delete all stored billables.</flux:text>
            </div>
            <flux:button size="sm" variant="danger" icon="trash" wire:click="removeBilling" wire:confirm="Delete all billing data?">Remove</flux:button>
        </div>
        <div class="flex items-center justify-between gap-4 py-2">
            <div>
                <flux:text class="font-medium text-zinc-800 dark:text-zinc-200">Purge alert data</flux:text>
                <flux:text size="sm">Delete all stored alerts.</flux:text>
            </div>
            <flux:button size="sm" variant="danger" icon="trash" wire:click="purgeAlertData" wire:confirm="Delete all alert data?">Purge</flux:button>
        </div>
    </div>
</x-card>