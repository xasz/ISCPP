<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\SCFirewall;
use App\Models\SCFirewallBackup;
use App\Services\SCService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Starts a full configuration export of one firewall and hands over to the poller.
 */
class CreateSCFirewallBackup implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    /**
     * Seconds before the first status check. Exports take a while on the firewall side.
     */
    public const FIRST_POLL_DELAY = 30;

    public $tries = 1;

    public function __construct(public string $firewallId, public string $requestedBy = 'schedule') {}

    public function handle(SCService $scService): void
    {
        $firewall = SCFirewall::with('SCTenant')->find($this->firewallId);

        if ($firewall === null || $firewall->SCTenant === null) {
            Event::log('scfirewalls', 'error', [
                'message' => 'Firewall backup skipped: firewall or tenant not found',
                'firewall' => $this->firewallId,
            ]);

            return;
        }

        $backup = SCFirewallBackup::create([
            'firewallId' => $firewall->id,
            'tenantId' => $firewall->tenantId,
            'status' => SCFirewallBackup::STATUS_PENDING,
            'requested_by' => $this->requestedBy,
        ]);

        try {
            $transactionId = $scService->firewallConfigExport($firewall->SCTenant, $firewall->id);
        } catch (\Throwable $exception) {
            $backup->update([
                'status' => SCFirewallBackup::STATUS_FAILED,
                'error' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            Event::logError('scfirewalls', 'Firewall backup could not be started', [
                'firewall' => $firewall->id,
                'SCTenant' => $firewall->tenantId,
                'error' => $exception->getMessage(),
            ]);

            return;
        }

        $backup->update(['transactionId' => $transactionId]);

        PollSCFirewallBackup::dispatch($backup->id)->delay(now()->addSeconds(self::FIRST_POLL_DELAY));

        Event::logInfo('scfirewalls', 'Firewall backup started', [
            'firewall' => $firewall->id,
            'SCTenant' => $firewall->tenantId,
            'transactionId' => $transactionId,
        ]);
    }

    public function uniqueId(): string
    {
        return 'create-firewall-backup-'.$this->firewallId;
    }
}
