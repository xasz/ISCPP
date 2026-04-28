<?php

namespace App\Console\Commands;

use App\Jobs\RefreshSCEndpoints;
use App\Models\Event;
use App\Models\SCTenant;
use Illuminate\Console\Command;

class QueueRefreshSCEndpointsJobsForAllTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:queue-refresh-scendpoints-jobs-for-all-tenants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh SC Endpoints for all tenants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Dispatching SCEndpointRefresh for all tenants');
        $tenants = SCTenant::notIgnored()->get();
        Event::logInfo('console', 'Dispatching SCEndpointRefresh for '.$tenants->count().' tenants');
        $tenants->each(function ($tenant) {
            RefreshSCEndpoints::dispatch($tenant);
        });
        $this->info('SCEndpointRefresh jobs dispatched for all tenants');
    }
}
