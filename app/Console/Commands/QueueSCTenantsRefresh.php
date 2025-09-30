<?php

namespace App\Console\Commands;

use App\Jobs\PushToNinjaSCTenantDownload;
use App\Jobs\RefreshSCTenantDownload;
use App\Jobs\RefreshSCTenantHealthscore;
use Illuminate\Console\Command;
use App\Jobs\RefreshSCAlerts;
use App\Jobs\RefreshSCTenants;
use App\Models\SCTenant;
use App\Models\Event;

class QueueSCTenantsRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:queue-sctenants-refresh';

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
        $this->info('Dispatching Refresh for all tenants');
        RefreshSCTenants::dispatch();
        $this->info('Refresh jobs dispatched for all tenants');
    }
}
