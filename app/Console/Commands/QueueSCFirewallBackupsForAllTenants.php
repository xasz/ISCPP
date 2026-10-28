<?php

namespace App\Console\Commands;

use App\Jobs\CreateSCFirewallBackup;
use App\Models\Event;
use App\Models\SCFirewall;
use Illuminate\Console\Command;

class QueueSCFirewallBackupsForAllTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:queue-scfirewall-backups-for-all-tenants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue a configuration backup for every connected firewall of every tenant not ignored by ISCPP';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // A disconnected firewall cannot export, so it would only produce a failed backup.
        $firewalls = SCFirewall::connected()
            ->whereHas('SCTenant', fn ($query) => $query->notIgnored())
            ->get();

        Event::logInfo('console', 'Dispatching firewall backups for '.$firewalls->count().' firewalls');

        $firewalls->each(fn (SCFirewall $firewall) => CreateSCFirewallBackup::dispatch($firewall->id, 'schedule'));

        $this->info($firewalls->count().' firewall backups queued');
    }
}
