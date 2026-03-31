<?php

namespace Database\Factories;

use App\Models\SCTenantHealthscore;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SCTenantHealthscore>
 */
class SCTenantHealthscoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tenantId = fake()->uuid();
        $timestamp = fake()->dateTimeBetween('-7 days', 'now');

        // Generate realistic raw data based on actual Sophos API structure
        $rawData = [
            'tenant' => [
                'id' => $tenantId,
            ],
            'timestamp' => $timestamp->format('Y-m-d\TH:i:s.000\Z'),
            'endpoint' => [
                'protection' => [
                    'computer' => [
                        'score' => fake()->numberBetween(40, 100),
                        'threats' => fake()->numberBetween(0, 10),
                        'clean' => fake()->numberBetween(100, 10000),
                        'compromised' => fake()->numberBetween(0, 5),
                    ],
                    'server' => [
                        'score' => fake()->numberBetween(40, 100),
                        'threats' => fake()->numberBetween(0, 5),
                        'clean' => fake()->numberBetween(50, 1000),
                        'compromised' => fake()->numberBetween(0, 3),
                    ],
                ],
                'policy' => [
                    'computer' => [
                        'threat-protection' => ['score' => fake()->numberBetween(40, 100)],
                        'anti-tampering' => ['score' => fake()->numberBetween(40, 100)],
                        'device-control' => ['score' => fake()->numberBetween(40, 100)],
                    ],
                    'server' => [
                        'server-threat-protection' => ['score' => fake()->numberBetween(40, 100)],
                        'anti-tampering' => ['score' => fake()->numberBetween(40, 100)],
                    ],
                ],
                'exclusions' => [
                    'policy' => [
                        'computer' => ['score' => fake()->numberBetween(40, 100), 'exclusions' => fake()->numberBetween(0, 50)],
                        'server' => ['score' => fake()->numberBetween(40, 100), 'exclusions' => fake()->numberBetween(0, 20)],
                    ],
                    'global' => ['score' => fake()->numberBetween(40, 100), 'exclusions' => fake()->numberBetween(0, 100)],
                ],
                'tamperProtection' => [
                    'computer' => [
                        'score' => fake()->numberBetween(40, 100),
                        'enabled' => fake()->boolean(80),
                        'incidents' => fake()->numberBetween(0, 5),
                    ],
                    'server' => [
                        'score' => fake()->numberBetween(40, 100),
                        'enabled' => fake()->boolean(80),
                        'incidents' => fake()->numberBetween(0, 2),
                    ],
                    'globalDetail' => ['score' => fake()->numberBetween(40, 100)],
                ],
                'mdrDataTelemetry' => [
                    'protectionImprovement' => [
                        'score' => fake()->numberBetween(40, 100),
                        'enrolled' => fake()->numberBetween(0, 1000),
                    ],
                ],
                'mdrAuthorizedContact' => [
                    'contact' => [
                        'score' => fake()->numberBetween(40, 100),
                        'configured' => fake()->boolean(50),
                    ],
                ],
            ],
            'firewall' => [
                'score' => fake()->numberBetween(40, 100),
                'connected' => fake()->numberBetween(0, 100),
                'disconnected' => fake()->numberBetween(0, 10),
            ],
            'email' => [
                'score' => fake()->numberBetween(40, 100),
                'threats_blocked' => fake()->numberBetween(0, 1000),
            ],
            'overallScore' => fake()->numberBetween(40, 100),
            'lastUpdated' => $timestamp->format('Y-m-d\TH:i:s.000\Z'),
            'links' => [
                'self' => [
                    'href' => 'https://api.central.sophos.com/healthscores/'.$tenantId,
                    'rel' => 'self',
                ],
            ],
        ];

        return [
            'tenantId' => $tenantId,
            'rawData' => $rawData,
        ];
    }
}
