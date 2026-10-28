<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SCEndpoint extends Model
{
    use HasFactory;

    protected $table = 'sc_endpoints';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'hostname',
        'tamperProtectionEnabled',
        'lastSeen',
        'tenantId',
        'type',
        'rawData',
        'healthStatus',
    ];

    protected $casts = [
        'tamperProtectionEnabled' => 'boolean',
        'lastSeen' => 'datetime',
        'rawData' => 'array',
    ];

    public function SCTenant()
    {
        return $this->belongsTo(SCTenant::class, 'tenantId');
    }

    /**
     * Map one endpoint object from the Sophos Endpoint API to the stored columns.
     *
     * Shared by the tenant wide refresh and the single endpoint refresh, so both store
     * exactly the same shape.
     *
     * @param  array<string, mixed>  $endpoint
     * @return array<string, mixed>
     */
    public static function attributesFromApi(array $endpoint, string $tenantId): array
    {
        return [
            'hostname' => $endpoint['hostname'],
            'tamperProtectionEnabled' => $endpoint['tamperProtectionEnabled'] ?? false,
            'lastSeen' => $endpoint['lastSeenAt'] ?? null,
            'tenantId' => $tenantId,
            'type' => $endpoint['type'],
            'rawData' => $endpoint,
            'healthStatus' => $endpoint['health']['overall'] ?? 'unknown',
        ];
    }

    public function isOnline(): bool
    {
        return (bool) data_get($this->rawData, 'online', false);
    }

    public function isIsolated(): bool
    {
        return data_get($this->rawData, 'isolation.status') === 'isolated';
    }

    public function groupId(): ?string
    {
        return data_get($this->rawData, 'group.id');
    }

    /**
     * Badge color for a Sophos health value (good, suspicious, bad, unknown).
     */
    public static function healthColor(?string $health): string
    {
        return match ($health) {
            'good' => 'green',
            'suspicious' => 'amber',
            'bad' => 'red',
            default => 'zinc',
        };
    }
}
