<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Per tenant, per score threshold and alerting state.
 *
 * A row is created lazily for every score of every tenant, so an absent override means
 * the configured default (100), not "unmonitored". Which tenants actually receive a
 * webhook is decided by the rule, not here.
 */
class SCTenantHealthscoreThreshold extends Model
{
    use HasFactory;

    public const STATE_UNKNOWN = 'unknown';

    public const STATE_OK = 'ok';

    public const STATE_LOW = 'low';

    protected $table = 'sc_tenant_healthscore_thresholds';

    protected $fillable = [
        'tenantId',
        'score_key',
        'threshold',
        'state',
        'last_value',
        'last_evaluated_at',
        'last_changed_at',
    ];

    protected function casts(): array
    {
        return [
            'threshold' => 'integer',
            'last_value' => 'integer',
            'last_evaluated_at' => 'datetime',
            'last_changed_at' => 'datetime',
        ];
    }

    public static function defaultThreshold(): int
    {
        return (int) config('iscpp.healthscore.default_threshold', 100);
    }

    public function SCTenant()
    {
        return $this->belongsTo(SCTenant::class, 'tenantId');
    }

    public function scoreLabel(): string
    {
        return Str::headline(str_replace('.', ' ', $this->score_key));
    }

    public function isOverridden(): bool
    {
        return $this->threshold !== self::defaultThreshold();
    }

    public function stateBadgeColor(): string
    {
        return match ($this->state) {
            self::STATE_LOW => 'red',
            self::STATE_OK => 'green',
            default => 'zinc',
        };
    }
}
