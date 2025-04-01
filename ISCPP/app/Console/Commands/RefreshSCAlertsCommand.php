<?php

namespace App\Console\Commands;

use App\Jobs\RefreshSCAlerts;
use App\Services\SCService;
use Illuminate\Console\Command;

class RefreshSCAlertsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh-scalerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the job to refresh SCAlerts';

    /**
     * Execute the console command.
     */
    public function handle(SCService $scService)
    {
        $this->info('Run Job: RefreshSCAlerts');
        $job = new RefreshSCAlerts();
        $job->handle($scService);
    }
}
