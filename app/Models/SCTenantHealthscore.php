<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class SCTenantHealthscore extends Model
{
    use HasFactory;

    protected $table = 'sc_tenant_healthscores';

    public static function AvailableScores(): array
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

    /**
     * The available scores grouped by area, with a short column label per score.
     * Keeps the order of AvailableScores().
     *
     * @return array<string, array<string, string>>
     */
    public static function ScoreGroups(): array
    {
        return [
            'Protection' => [
                'protection.computer.score' => 'Computer',
                'protection.server.score' => 'Server',
            ],
            'Policy' => [
                'policy.computer.threat-protection.score' => 'Computer',
                'policy.server.server-threat-protection.score' => 'Server',
            ],
            'Exclusions' => [
                'exclusions.policy.computer.score' => 'Computer',
                'exclusions.policy.server.score' => 'Server',
                'exclusions.global.score' => 'Global',
            ],
            'Tamper Protection' => [
                'tamperProtection.computer.score' => 'Computer',
                'tamperProtection.server.score' => 'Server',
                'tamperProtection.globalDetail.score' => 'Global',
            ],
            'MDR' => [
                'mdrDataTelemetry.protectionImprovement.score' => 'Telemetry',
                'mdrAuthorizedContact.contact.score' => 'Contact',
            ],
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

    public function hasScores(): bool
    {
        return $this->rawData && isset($this->rawData['endpoint']) && count($this->rawData['endpoint']) > 0;
    }

    public function getScores(): Collection
    {
        $results = [];
        collect(self::AvailableScores())->each(function ($scorePath) use (&$results) {
            $keys = explode('.', 'endpoint.'.$scorePath);
            $value = $this->rawData;
            foreach ($keys as $key) {
                if (! isset($value[$key])) {
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
