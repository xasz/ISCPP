<?php

namespace App\Console\Commands;

use App\Jobs\RefreshSCTenantHealthscore;
use Illuminate\Console\Command;
use App\Jobs\RefreshSCAlerts;
use App\Models\SCTenant;
use App\Models\Event;

class QueueRefreshSCHealthscoresForAllTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:queue-refresh-healthscores-jobs-for-all-tenants';

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
        $this->info('Dispatching SCHealthscores for all tenants');
        $tenants = SCTenant::all();
        Event::logInfo("console", "Dispatching SCHealthscore for ". $tenants->count() . " tenants");
        $tenants->each(function ($tenant) {
            RefreshSCTenantHealthscore::dispatch($tenant);
        });
        $this->info('SCHealthscore jobs dispatched for all tenants');
    }
}
