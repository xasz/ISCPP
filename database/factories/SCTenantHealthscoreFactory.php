<?php

namespace Database\Factories;

use App\Models\SCTenantHealthscore;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SCTenantHealthscore>
 */
class SCTenantHealthscoreFactory extends Factory
{
    public function definition(): array
    {
        $rawData =  [
            'endpoint' => [
                'protection' => [
                    'computer' => [
                        'score' => fake()->numberBetween(0, 100),
                        'total' => fake()->numberBetween(0, 200),
                        'notFullyProtected' => fake()->numberBetween(0, 50),
                        'snoozed' => fake()->boolean(),
                    ],
                    'server' => [
                        'score' => fake()->numberBetween(0, 100),
                        'total' => fake()->numberBetween(0, 50),
                        'notFullyProtected' => fake()->numberBetween(0, 20),
                        'snoozed' => fake()->boolean(),
                    ],
                ],
                'policy' => [
                    'computer' => [
                        'threat-protection' => [
                            'score' => fake()->numberBetween(0, 100),
                            'total' => fake()->numberBetween(0, 10),
                            'notOnRecommended' => fake()->numberBetween(0, 5),
                            'snoozed' => fake()->boolean(),
                            'policies' => [],
                        ],
                    ],
                    'server' => [
                        'server-threat-protection' => [
                            'score' => fake()->numberBetween(0, 100),
                            'total' => fake()->numberBetween(0, 10),
                            'notOnRecommended' => fake()->numberBetween(0, 5),
                            'snoozed' => fake()->boolean(),
                            'policies' => [],
                        ],
                    ],
                ],
                'exclusions' => [
                    'policy' => [
                        'computer' => [
                            'score' => fake()->numberBetween(0, 100),
                            'total' => fake()->numberBetween(0, 10),
                            'numberOfSecurityRisks' => fake()->numberBetween(0, 5),
                            'snoozed' => fake()->boolean(),
                        ],
                        'server' => [
                            'score' => fake()->numberBetween(0, 100),
                            'total' => fake()->numberBetween(0, 10),
                            'numberOfSecurityRisks' => fake()->numberBetween(0, 5),
                            'snoozed' => fake()->boolean(),
                        ],
                    ],
                    'global' => [
                        'score' => fake()->numberBetween(0, 100),
                        'numberOfSecurityRisks' => fake()->numberBetween(0, 5),
                        'lockedByManagingAccount' => fake()->boolean(),
                        'snoozed' => fake()->boolean(),
                    ],
                ],
                'tamperProtection' => [
                    'computer' => [
                        'score' => fake()->numberBetween(0, 100),
                        'total' => fake()->numberBetween(0, 200),
                        'disabled' => fake()->numberBetween(0, 50),
                        'snoozed' => fake()->boolean(),
                    ],
                    'server' => [
                        'score' => fake()->numberBetween(0, 100),
                        'total' => fake()->numberBetween(0, 50),
                        'disabled' => fake()->numberBetween(0, 20),
                        'snoozed' => fake()->boolean(),
                    ],
                    'globalDetail' => [
                        'score' => fake()->numberBetween(0, 100),
                        'enabled' => fake()->boolean(),
                        'snoozed' => fake()->boolean(),
                    ],
                    'global' => fake()->boolean(),
                ],
                'mdrDataTelemetry' => [
                    'protectionImprovement' => [
                        'total' => fake()->numberBetween(0, 10),
                        'missingSettings' => [],
                        'score' => fake()->numberBetween(0, 100),
                        'snoozed' => fake()->boolean(),
                    ],
                ],                
                'mdrAuthorizedContact' => [
                    'contact' => [
                        'configured' => fake()->boolean(),
                        'score' => fake()->numberBetween(0, 100),
                        'snoozed' => fake()->boolean(),
                    ],
                ]
            ]
        ];

        return [
           'rawData' => $rawData
        ];
    }
}
