<?php

namespace Database\Factories;

use App\Events\ISCPPEventSeverity;
use App\Models\ISCPPEventLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ISCPPEventLog>
 */
class ISCPPEventLogFactory extends Factory
{
    public function definition(): array
    {
        $uuid = (string) Str::uuid7();
        $key = 'sc.tenant.created';

        return [
            'uuid' => $uuid,
            'key' => $key,
            'severity' => ISCPPEventSeverity::Info,
            'tenantId' => null,
            'subject_type' => null,
            'subject_id' => null,
            'occurred_at' => now(),
            'payload' => [
                'iscpp' => ['payloadVersion' => 1, 'instance' => config('app.url')],
                'event' => [
                    'id' => $uuid,
                    'key' => $key,
                    'label' => 'SC Tenant Created',
                    'severity' => 'info',
                    'occurredAt' => now()->toIso8601String(),
                ],
                'tenant' => null,
                'subject' => null,
                'data' => [],
            ],
        ];
    }
}
