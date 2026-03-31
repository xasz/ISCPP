<?php

namespace App\Console\Commands;

use App\Jobs\PushToNinjaSCTenantDownload;
use App\Models\Event;
use App\Models\SCTenant;
use Illuminate\Console\Command;

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
    protected $description = 'Refresh SC Downloads for all tenants with ninjaorg_id != -1 and push to Ninja';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Dispatching PushToNinjaSCTenantDownload for all tenants with ninjaorg_id != -1');
        $tenants = SCTenant::where('ninjaorg_id', '!=', -1)->get();
        Event::logInfo('console', 'Dispatching PushToNinjaSCTenantDownload for '.$tenants->count().' tenants');
        $tenants->each(function ($tenant) {
            PushToNinjaSCTenantDownload::dispatch($tenant);
        });
        $this->info('PushToNinjaSCTenantDownload jobs dispatched for all tenants with ninjaorg_id != -1');
    }
}
