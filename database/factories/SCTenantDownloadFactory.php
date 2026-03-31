<?php

namespace Database\Factories;

use App\Models\SCTenantDownload;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SCTenantDownload>
 */
class SCTenantDownloadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tenantId = fake()->uuid();

        // Generate realistic raw data based on actual Sophos API structure
        $rawData = [
            'tenant' => [
                'id' => $tenantId,
            ],
            'installers' => [
                [
                    'platform' => 'windows',
                    'type' => 'computer',
                    'name' => 'Sophos Endpoint Protection - Windows Computer',
                    'version' => fake()->randomElement(['10', '11', '12']).'.'.fake()->numberBetween(0, 9).'.'.fake()->numberBetween(0, 99),
                    'downloadUrl' => 'https://downloads.sophos.com/endpointprotection/windows-computer-'.fake()->uuid().'.exe',
                    'size' => fake()->numberBetween(50000000, 500000000),
                    'sha256' => strtoupper(fake()->sha256()),
                    'releaseDate' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
                ],
                [
                    'platform' => 'linux',
                    'type' => 'server',
                    'name' => 'Sophos Endpoint Protection - Linux Server',
                    'version' => fake()->randomElement(['10', '11', '12']).'.'.fake()->numberBetween(0, 9).'.'.fake()->numberBetween(0, 99),
                    'downloadUrl' => 'https://downloads.sophos.com/endpointprotection/linux-server-'.fake()->uuid().'.tar.gz',
                    'size' => fake()->numberBetween(50000000, 500000000),
                    'sha256' => strtoupper(fake()->sha256()),
                    'releaseDate' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
                ],
                [
                    'platform' => 'macOS',
                    'type' => 'computer',
                    'name' => 'Sophos Endpoint Protection - macOS',
                    'version' => fake()->randomElement(['10', '11', '12']).'.'.fake()->numberBetween(0, 9).'.'.fake()->numberBetween(0, 99),
                    'downloadUrl' => 'https://downloads.sophos.com/endpointprotection/macos-computer-'.fake()->uuid().'.dmg',
                    'size' => fake()->numberBetween(50000000, 500000000),
                    'sha256' => strtoupper(fake()->sha256()),
                    'releaseDate' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
                ],
            ],
            'lastUpdated' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d\TH:i:s.000\Z'),
            'links' => [
                'self' => [
                    'href' => 'https://api.central.sophos.com/downloads/'.$tenantId,
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
