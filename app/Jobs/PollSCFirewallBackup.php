<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\SCFirewallBackup;
use App\Services\SCService;
use App\Settings\SCServiceSettings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

/**
 * Checks an export transaction and, once it has finished, downloads the configuration.
 *
 * While the firewall is still exporting, the job dispatches its successor with a delay
 * instead of releasing itself, so the polling budget is explicit and does not depend on
 * the queue's retry settings.
 */
class PollSCFirewallBackup implements ShouldQueue
{
    use Queueable;

    public const POLL_INTERVAL = 60;

    /** About 30 minutes of polling before the export is given up. */
    public const MAX_POLLS = 30;

    public $tries = 1;

    public function __construct(public int $backupId, public int $poll = 1) {}

    public function handle(SCService $scService): void
    {
        $backup = SCFirewallBackup::with(['SCFirewall', 'SCTenant'])->find($this->backupId);

        if ($backup === null || $backup->status !== SCFirewallBackup::STATUS_PENDING) {
            return;
        }

        if ($backup->SCTenant === null || $backup->transactionId === null) {
            $this->markFailed($backup, 'Tenant or export transaction missing');

            return;
        }

        try {
            $transaction = $scService->firewallConfigTransaction($backup->SCTenant, $backup->firewallId, $backup->transactionId);
        } catch (\Throwable $exception) {
            $this->retryOrFail($backup, $exception->getMessage());

            return;
        }

        $status = $transaction['status'] ?? null;
        $result = $transaction['result'] ?? null;

        if ($status !== 'finished') {
            $this->retryOrFail($backup, 'Export did not finish in time (last status: '.($status ?? 'unknown').')');

            return;
        }

        $url = data_get($transaction, 'response.url');

        if ($result !== 'success' || ! is_string($url) || $url === '') {
            $this->markFailed($backup, 'Export finished with result "'.($result ?? 'unknown').'": '.json_encode($transaction['response'] ?? null));

            return;
        }

        try {
            $contents = $scService->downloadFirewallConfigExport($url);
        } catch (\Throwable $exception) {
            $this->markFailed($backup, $exception->getMessage());

            return;
        }

        $path = $this->storagePath($backup);
        Storage::disk(SCFirewallBackup::DISK)->put($path, $contents);

        $backup->update([
            'status' => SCFirewallBackup::STATUS_FINISHED,
            'file_path' => $path,
            'file_size' => strlen($contents),
            'sha256' => hash('sha256', $contents),
            'error' => null,
            'finished_at' => now(),
        ]);

        SCFirewallBackup::pruneFor($backup->firewallId, (int) resolve(SCServiceSettings::class)->firewallBackupsRetention);

        Event::logInfo('scfirewalls', 'Firewall backup stored', [
            'firewall' => $backup->firewallId,
            'SCTenant' => $backup->tenantId,
            'size' => strlen($contents),
        ]);
    }

    private function retryOrFail(SCFirewallBackup $backup, string $reason): void
    {
        if ($this->poll >= self::MAX_POLLS) {
            $this->markFailed($backup, $reason);

            return;
        }

        self::dispatch($backup->id, $this->poll + 1)->delay(now()->addSeconds(self::POLL_INTERVAL));
    }

    private function markFailed(SCFirewallBackup $backup, string $reason): void
    {
        $backup->update([
            'status' => SCFirewallBackup::STATUS_FAILED,
            'error' => $reason,
            'finished_at' => now(),
        ]);

        Event::logError('scfirewalls', 'Firewall backup failed', [
            'firewall' => $backup->firewallId,
            'SCTenant' => $backup->tenantId,
            'error' => $reason,
        ]);
    }

    private function storagePath(SCFirewallBackup $backup): string
    {
        $hostname = preg_replace('/[^A-Za-z0-9._-]/', '_', (string) ($backup->SCFirewall?->hostname ?? 'firewall'));

        return sprintf(
            'firewall-backups/%s/%s/%s_%s_%d.tar.gz',
            $backup->tenantId,
            $backup->firewallId,
            now()->format('Ymd-His'),
            $hostname,
            $backup->id,
        );
    }
}
