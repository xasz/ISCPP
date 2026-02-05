<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\RefreshSCAlerts;
use App\Models\SCTenant;
use App\Models\Event;

class QueueRefreshAllSCAlertsJobsForAllTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue-refresh-all-scalerts-jobs-for-all-tenants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue refresh all SCAlerts jobs for all tenants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Dispatching SCAlertRefresh for all tenants');
        $tenants = SCTenant::all();
        Event::logInfo("console", "Dispatching SCAlertRefresh ALL for ". $tenants->count() . " tenants");
        $tenants->each(function ($tenant) {
            RefreshSCAlerts::dispatch($tenant, true);
        });
        $this->info('SCAlertRefresh jobs dispatched for '. $tenants->count() . ' tenants');
    }
}
