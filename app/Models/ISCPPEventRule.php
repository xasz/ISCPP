<?php

namespace App\Models;

use App\Services\ISCPPEventRegistry;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * One event, one tenant scope, one webhook.
 *
 * The tenant scope is all tenants, a single tenant (tenantId) or a tenant group
 * (sc_tenant_group_id). At most one of the two columns is set.
 *
 * Matching and delivery live on one model because a rule always has exactly one webhook.
 * Two rules wanting the same endpoint simply repeat the URL.
 */
class ISCPPEventRule extends Model
{
    use HasFactory;

    protected $table = 'iscpp_event_rules';

    protected $fillable = [
        'name',
        'enabled',
        'event_key',
        'tenantId',
        'sc_tenant_group_id',
        'url',
        'basic_auth_username',
        'basic_auth_password',
        'consecutive_failures',
        'disabled_at',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            // Cast, not manual encrypt(): attribute access already returns plaintext.
            'basic_auth_password' => 'encrypted',
            'disabled_at' => 'datetime',
        ];
    }

    public function conditions()
    {
        return $this->hasMany(ISCPPEventRuleCondition::class, 'iscpp_event_rule_id');
    }

    public function deliveries()
    {
        return $this->hasMany(ISCPPEventDelivery::class, 'iscpp_event_rule_id');
    }

    public function SCTenant()
    {
        return $this->belongsTo(SCTenant::class, 'tenantId');
    }

    public function SCTenantGroup()
    {
        return $this->belongsTo(SCTenantGroup::class, 'sc_tenant_group_id');
    }

    /**
     * Enabled rules, memoised briefly so recording an event does not query per event.
     *
     * @return Collection<int, ISCPPEventRule>
     */
    public static function cachedEnabled(): Collection
    {
        return Cache::driver('array')->remember('iscpp:rules:enabled', 10, fn () => static::query()
            ->where('enabled', true)
            ->with(['conditions', 'SCTenantGroup.tenants:id'])
            ->get());
    }

    public static function flushCache(): void
    {
        Cache::driver('array')->forget('iscpp:rules:enabled');
    }

    public function matches(ISCPPEventLog $event): bool
    {
        if (! $this->enabled) {
            return false;
        }

        if ($this->event_key !== $event->key) {
            return false;
        }

        if (! $this->matchesTenant($event)) {
            return false;
        }

        return $this->conditions->every(fn (ISCPPEventRuleCondition $condition) => $condition->passes($event->payload ?? []));
    }

    /**
     * A rule with a tenant matches only that tenant.
     * A rule with a tenant group matches only the group's members, never events without a tenant.
     * A rule with neither matches every tenant, and also events that have no tenant.
     */
    public function matchesTenant(ISCPPEventLog $event): bool
    {
        if ($this->tenantId !== null) {
            return $this->tenantId === $event->tenantId;
        }

        if ($this->sc_tenant_group_id !== null) {
            return (bool) $this->SCTenantGroup?->hasTenant($event->tenantId);
        }

        return true;
    }

    public function eventLabel(): string
    {
        return app(ISCPPEventRegistry::class)->labelFor($this->event_key);
    }

    /**
     * Record a terminal delivery failure and open the circuit breaker if needed.
     *
     * Only terminal failures count. Counting every retry would let a single dead event
     * burn half the budget and disable a healthy endpoint after one bad hour.
     */
    public function registerFailure(): void
    {
        $this->increment('consecutive_failures');
        $this->refresh();

        $threshold = (int) config('iscpp.webhook.failure_threshold', 10);

        if ($this->enabled && $this->consecutive_failures >= $threshold) {
            $this->forceFill(['enabled' => false, 'disabled_at' => now()])->save();
            self::flushCache();
        }
    }

    public function registerSuccess(): void
    {
        if ($this->consecutive_failures !== 0) {
            $this->forceFill(['consecutive_failures' => 0])->save();
        }
    }

    public function reenable(): void
    {
        $this->forceFill([
            'enabled' => true,
            'consecutive_failures' => 0,
            'disabled_at' => null,
        ])->save();

        self::flushCache();
    }

    public function wasAutoDisabled(): bool
    {
        return ! $this->enabled && $this->disabled_at !== null;
    }
}
