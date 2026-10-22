<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class SCTenantHealthscore extends Model
{
    use HasFactory;

    protected $table = 'sc_tenant_healthscores';

    public static function AvailableScores() : array
    {
        return [
            'protection.computer.score',
            'protection.server.score',
            'policy.computer.threat-protection.score',
            'policy.server.server-threat-protection.score',
            'exclusions.policy.computer.score',
            'exclusions.policy.server.score',
            'exclusions.global.score',
            'tamperProtection.computer.score',
            'tamperProtection.server.score',
            'tamperProtection.globalDetail.score',
            'mdrDataTelemetry.protectionImprovement.score',
            'mdrAuthorizedContact.contact.score',
        ];
    }

    protected $fillable = [
        'rawData',
        'tenantId',
    ];

    protected $casts = [
        'rawData' => 'array',
    ];

    public function SCTenant()
    {
        return $this->belongsTo(SCTenant::class, 'tenantId');
    }

    public function hasScores() : bool
    {
        return $this->rawData && isset($this->rawData['endpoint']) && count($this->rawData['endpoint']) > 0;
    }
    
    public function getScores() : Collection
    {
        $results = [];
        collect(self::AvailableScores())->each(function ($scorePath) use (&$results) {
            $keys = explode('.', 'endpoint.' . $scorePath);
            $value = $this->rawData;
            foreach ($keys as $key) {
                if (!isset($value[$key])) {
                    $value = -1;
                    break;
                }
                $value = $value[$key];
            }
            $results[$scorePath] = $value;
        });
        return collect($results);

    }
    public function getEndpointMDRContactHealthscore()
    {
        if (! $this->hasEndpointMDRContactHealthscore()) {
            return -1;
        }

        return $this->rawData['endpoint']['mdrAuthorizedContact']['contact']['score'] ?? -1;
    }
}
