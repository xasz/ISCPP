<?php

namespace App\Models;

use App\Events\ISCPPEventSeverity;
use App\Services\ISCPPEventRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/**
 * A recorded ISCPP event.
 *
 * This is the new event log and is completely separate from the legacy App\Models\Event
 * system log. The payload column holds the frozen JSON envelope exactly as it was sent,
 * so a later tenant rename or deletion cannot alter history.
 */
class ISCPPEventLog extends Model
{
    use HasFactory;
    use MassPrunable;

    protected $table = 'iscpp_events';

    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'key',
        'severity',
        'tenantId',
        'subject_type',
        'subject_id',
        'payload',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'occurred_at' => 'datetime',
            'severity' => ISCPPEventSeverity::class,
        ];
    }

    /**
     * Deliveries cascade at database level, so only this model is prunable.
     */
    public function prunable(): Builder
    {
        return static::where('occurred_at', '<=', now()->subDays((int) config('iscpp.events.retention_days', 90)));
    }

    public function SCTenant()
    {
        return $this->belongsTo(SCTenant::class, 'tenantId');
    }

    public function deliveries()
    {
        return $this->hasMany(ISCPPEventDelivery::class, 'iscpp_event_id');
    }

    public function label(): string
    {
        return app(ISCPPEventRegistry::class)->labelFor($this->key);
    }

    /**
     * The event specific attributes, without the envelope wrapper.
     *
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return $this->payload['data'] ?? [];
    }

    public function scopeOfKey(Builder $query, ?string $key): Builder
    {
        return $query->when($key, fn (Builder $q) => $q->where('key', $key));
    }

    public function scopeOfSeverity(Builder $query, ?string $severity): Builder
    {
        return $query->when($severity, fn (Builder $q) => $q->where('severity', $severity));
    }

    public function scopeOfTenant(Builder $query, ?string $tenantId): Builder
    {
        return $query->when($tenantId, fn (Builder $q) => $q->where('tenantId', $tenantId));
    }
}
