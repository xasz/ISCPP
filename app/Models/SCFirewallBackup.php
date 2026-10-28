<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * One configuration export of a firewall.
 *
 * Sophos exports asynchronously: the export call returns a transaction, which finishes
 * with a pre-signed download link. The file is then kept on the local disk.
 */
class SCFirewallBackup extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_FINISHED = 'finished';

    public const STATUS_FAILED = 'failed';

    public const DISK = 'local';

    protected $table = 'sc_firewall_backups';

    protected $fillable = [
        'firewallId',
        'tenantId',
        'status',
        'transactionId',
        'file_path',
        'file_size',
        'sha256',
        'error',
        'requested_by',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'finished_at' => 'datetime',
        ];
    }

    public function SCFirewall(): BelongsTo
    {
        return $this->belongsTo(SCFirewall::class, 'firewallId');
    }

    public function SCTenant(): BelongsTo
    {
        return $this->belongsTo(SCTenant::class, 'tenantId');
    }

    public function isDownloadable(): bool
    {
        return $this->status === self::STATUS_FINISHED
            && $this->file_path !== null
            && Storage::disk(self::DISK)->exists($this->file_path);
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_FINISHED => 'green',
            self::STATUS_FAILED => 'red',
            default => 'sky',
        };
    }

    /**
     * Delete the stored file together with the row.
     */
    public function deleteWithFile(): void
    {
        if ($this->file_path !== null) {
            Storage::disk(self::DISK)->delete($this->file_path);
        }

        $this->delete();
    }

    /**
     * Keep the newest $keep finished backups of a firewall and drop the rest. Failed
     * attempts are kept for 30 days so a broken schedule stays visible.
     */
    public static function pruneFor(string $firewallId, int $keep): void
    {
        $keep = max(1, $keep);

        static::where('firewallId', $firewallId)
            ->where('status', self::STATUS_FINISHED)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->skip($keep)
            ->take(PHP_INT_MAX)
            ->get()
            ->each->deleteWithFile();

        static::where('firewallId', $firewallId)
            ->where('status', self::STATUS_FAILED)
            ->where('created_at', '<', now()->subDays(30))
            ->get()
            ->each->deleteWithFile();
    }
}
