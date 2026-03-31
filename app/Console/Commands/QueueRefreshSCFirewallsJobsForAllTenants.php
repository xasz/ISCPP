<?php

namespace App\Console\Commands;

use App\Jobs\RefreshSCFirewalls;
use App\Models\Event;
use App\Models\SCTenant;
use Illuminate\Console\Command;

class QueueRefreshSCFirewallsJobsForAllTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:queue-refresh-scfirewalls-jobs-for-all-tenants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh SC Firewalls for all tenants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Dispatching SCFirewallRefresh for all tenants');
        $tenants = SCTenant::all();
        Event::logInfo('console', 'Dispatching SCFirewallRefresh for '.$tenants->count().' tenants');
        $tenants->each(function ($tenant) {
            RefreshSCFirewalls::dispatch($tenant);
        });
        $this->info('SCFirewallRefresh jobs dispatched for all tenants');
    }
}
