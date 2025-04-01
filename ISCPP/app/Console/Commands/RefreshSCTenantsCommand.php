<?php

namespace App\Console\Commands;

use App\Jobs\RefreshSCTenants;
use App\Models\SCAlert;
use App\Models\SCTenant;
use App\Models\User;
use App\Services\SCService;
use Illuminate\Console\Command;

class RefreshSCTenantsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh-sctenants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the job to refresh SCTenants';

    /**
     * Execute the console command.
     */
    public function handle(SCService $scService)
    {
        $this->info('Run Job: RefreshSCTenants');
        $job = new RefreshSCTenants();
        $job->handle($scService);
    }
}
