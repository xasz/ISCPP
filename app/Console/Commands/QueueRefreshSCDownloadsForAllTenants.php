<?php

namespace App\Console\Commands;

use App\Jobs\RefreshSCTenantDownload;
use App\Jobs\RefreshSCTenantHealthscore;
use Illuminate\Console\Command;
use App\Jobs\RefreshSCAlerts;
use App\Models\SCTenant;
use App\Models\Event;

class QueueRefreshSCDownloadsForAllTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:queue-refresh-downloads-jobs-for-all-tenants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Dispatching SCTenantDownload for all tenants');
        $tenants = SCTenant::all();
        Event::logInfo("console", "Dispatching SCTenantDownload for ". $tenants->count() . " tenants");
        $tenants->each(function ($tenant) {
            RefreshSCTenantDownload::dispatch($tenant);
        });
        $this->info('SCTenantDownload jobs dispatched for all tenants');
    }
}
