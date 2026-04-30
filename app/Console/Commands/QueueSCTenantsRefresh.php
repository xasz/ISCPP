<?php

namespace App\Console\Commands;

use App\Jobs\RefreshSCTenants;
use Illuminate\Console\Command;

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
    protected $description = 'Refresh SC Tenants for all tenants';

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
