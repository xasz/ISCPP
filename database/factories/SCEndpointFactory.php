<?php

namespace Database\Factories;

use App\Models\SCEndpoint;
use App\Models\SCTenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SCEndpoint>
 */
class SCEndpointFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $lastSeen = fake()->dateTimeThisYear();

        $rawData = [
            'id' => (string) Str::uuid(),
            'type' => fake()->randomElement(['computer', 'server']),
            'tenant' => [
                'id' => (string) Str::uuid(),
            ],
            'hostname' => fake()->domainWord(),
            'health' => [
                'overall' => fake()->randomElement(['good']),
                'threats' => [
                    'status' => fake()->randomElement(['good']),
                ],
            ],

            'os' => fake()->randomElement([
                [
                    'isServer' => false,
                    'platform' => 'windows',
                    'name' => 'Windows 11 Home ',
                    'majorVersion' => 11,
                    'minorVersion' => 0,
                    'build' => 22621,
                ],
                [
                    'isServer' => true,
                    'platform' => 'windows',
                    'name' => 'Windows Server 2022',
                    'majorVersion' => 10,
                    'minorVersion' => 0,
                    'build' => 20348,
                ],
            ]),
            'ipv4Addresses' => [fake()->ipv4()],
            'ipv6Addresses' => [fake()->ipv6()],
            'macAddresses' => [fake()->macAddress()],
            'mdrManaged' => fake()->boolean(),
            'associatedPerson' => [
                'name' => fake()->name(),
                'viaLogin' => fake()->userName(),
                'id' => (string) Str::uuid(),
            ],
            'tamperProtectionSupported' => fake()->boolean(),
            'tamperProtectionEnabled' => fake()->boolean(),
            'assignedProducts' => [
                [
                    'code' => 'endpointProtection',
                    'version' => '2025.2.3.8.0',
                    'status' => fake()->randomElement(['installed', 'notInstalled']),
                ],
                [
                    'code' => 'deviceEncryption',
                    'version' => '2025.1.3.2.0',
                    'status' => fake()->randomElement(['installed', 'notInstalled']),
                ],
                [
                    'code' => 'interceptX',
                    'version' => '2024.1.2.1.0',
                    'status' => fake()->randomElement(['installed', 'notInstalled']),
                ],
                [
                    'code' => 'coreAgent',
                    'version' => '2025.2.3.8.0',
                    'status' => fake()->randomElement(['installed', 'notInstalled']),
                ],
                [
                    'code' => 'xdr',
                    'version' => '2025.0.0.0.0',
                    'status' => fake()->randomElement(['installed', 'notInstalled']),
                ],
                [
                    'code' => 'ztna',
                    'version' => '2025.0.0.0.0',
                    'status' => fake()->randomElement(['installed', 'notInstalled']),
                ],
            ],
            'packages' => [
                'protection' => [
                    'assignedId' => 'Endpoint',
                    'name' => 'Endpoint',
                    'status' => fake()->randomElement(['assigned', 'unassigned']),
                    'available' => [
                        [
                            'id' => 'Endpoint',
                            'name' => 'Endpoint',
                        ],
                    ],
                ],
                'ztna' => [
                    'status' => fake()->randomElement(['assigned', 'unassigned']),
                ],
                'encryption' => [
                    'status' => fake()->randomElement(['assigned', 'unassigned']),
                    'available' => [
                        [
                            'id' => 'Encryption',
                            'name' => 'Encryption',
                        ],
                    ],
                ],
            ],
            'lastSeenAt' => $lastSeen->format('Y-m-d\TH:i:s.v\Z'),
            'lockdown' => [
                'status' => fake()->randomElement(['available', 'unavailable']),
            ],
            'tags' => [],
            'online' => fake()->boolean(),
            'isolation' => [
                'status' => fake()->randomElement(['notIsolated', 'isolated']),
                'adminIsolated' => fake()->boolean(),
                'selfIsolated' => fake()->boolean(),
            ],
            'modules' => [
                [
                    'name' => 'coreAgent',
                    'version' => '2025.2.3.8.0',
                ],
                [
                    'name' => 'interceptX',
                    'version' => '2024.1.2.1.0',
                ],
                [
                    'name' => 'deviceEncryption',
                    'version' => '2025.1.3.2.0',
                ],
            ],
            'registeredAt' => fake()->dateTimeThisYear()->format('Y-m-d\TH:i:s.v\Z'),
        ];

        return [
            'id' => $rawData['id'],
            'hostname' => $rawData['hostname'],
            'tamperProtectionEnabled' => $rawData['tamperProtectionEnabled'],
            'lastSeen' => $lastSeen,
            'tenantId' => $rawData['tenant']['id'],
            'type' => $rawData['type'],
            'healthStatus' => $rawData['health']['overall'],
            'rawData' => $rawData,
        ];
    }

    public function forTenant(SCTenant $tenant)
    {
        return $this->state(function (array $attributes) use ($tenant) {
            return [
                'rawData' => array_merge($attributes['rawData'], [
                    'tenant' => [
                        'id' => $tenant->id,
                    ],
                ]),
                'tenantId' => $tenant->id,
            ];
        });
    }
}
