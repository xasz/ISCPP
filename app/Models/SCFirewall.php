<?php

namespace App\Models;

use App\Services\SCService;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SCFirewall extends Model
{
    use HasFactory;

    protected $table = 'sc_firewalls';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'tenantId',
        'hostname',
        'rawData',
        'plannedFirmwareUpgradeAt',
    ];

    protected $casts = [
        'rawData' => 'array',
        'plannedFirmwareUpgradeAt' => 'datetime',
    ];

    public function SCTenant()
    {
        return $this->belongsTo(SCTenant::class, 'tenantId');
    }

    public function backups(): HasMany
    {
        return $this->hasMany(SCFirewallBackup::class, 'firewallId');
    }

    public function isConnected(): bool
    {
        return (bool) data_get($this->rawData, 'status.connected', false);
    }

    public function scopeConnected(Builder $query): Builder
    {
        return $query->where('rawData->status->connected', true);
    }

    public function scopeDisconnected(Builder $query): Builder
    {
        return $query->where('rawData->status->connected', false);
    }

    public function scopeSuspended(Builder $query): Builder
    {
        return $query->where('rawData->status->suspended', true);
    }

    public function checkFirmwareUpgrade(): array
    {
        return app(SCService::class)->firewallFirmwareUpgradeCheck($this->SCTenant, [$this->id]);
    }

    public function scheduleFirmwareUpgrade(?string $upgradeToVersion, CarbonInterface $upgradeAt): array
    {
        return app(SCService::class)->firewallFirmwareUpgradePlan(
            $this->SCTenant,
            [$this->id],
            $upgradeToVersion,
            $upgradeAt,
        );
    }

    public function cancelFirmwareUpgrade(): array
    {
        return app(SCService::class)->firewallFirmwareUpgradeCancel($this->SCTenant, [$this->id]);
    }
}
