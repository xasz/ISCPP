<?php

namespace App\Console\Commands;

use App\Jobs\PushToNinjaSCTenantDownload;
use App\Jobs\RefreshSCTenantDownload;
use App\Jobs\RefreshSCTenantHealthscore;
use Illuminate\Console\Command;
use App\Jobs\RefreshSCAlerts;
use App\Models\SCTenant;
use App\Models\Event;

class QueueSCDownloadsNinjaPush extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:queue-scdownloads-ninja-push';

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
        $this->info('Dispatching PushToNinjaSCTenantDownload for all tenants with ninjaorg_id != -1');
        $tenants = SCTenant::where('ninjaorg_id', '!=', -1)->get();
        Event::logInfo("console", "Dispatching PushToNinjaSCTenantDownload for ". $tenants->count() . " tenants");
        $tenants->each(function ($tenant) {
            PushToNinjaSCTenantDownload::dispatch($tenant);
        });
        $this->info('PushToNinjaSCTenantDownload jobs dispatched for all tenants');
    }
}
