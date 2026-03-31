<?php

namespace Database\Factories;

use App\Models\SCFirewall;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SCFirewall>
 */
class SCFirewallFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firewallId = fake()->unique()->uuid();
        $tenantId = fake()->uuid();
        $serialNumber = strtoupper(fake()->bothify('???###????'));
        $hostname = fake()->word();
        $ipAddress1 = fake()->ipv4();
        $ipAddress2 = fake()->boolean(40) ? fake()->ipv4() : null;
        $createdAt = fake()->dateTimeBetween('-2 years', '-1 year');
        $updatedAt = fake()->dateTimeBetween($createdAt, 'now');

        // Build realistic raw data matching actual Sophos API structure
        $rawData = [
            'id' => $firewallId,
            'cluster' => fake()->boolean(20) ? [
                'id' => fake()->uuid(),
                'mode' => 'activeActive',
                'status' => fake()->randomElement(['primary', 'secondary']),
                'peers' => [
                    'id' => fake()->uuid(),
                    'serialNumber' => strtoupper(fake()->bothify('???###????')),
                ],
            ] : null,
            'tenant' => [
                'id' => $tenantId,
            ],
            'serialNumber' => $serialNumber,
            'group' => fake()->boolean(30) ? [
                'id' => fake()->uuid(),
                'name' => fake()->word().' Group',
            ] : null,
            'hostname' => $hostname,
            'name' => fake()->word().'-label',
            'externalIpv4Addresses' => array_filter([$ipAddress1, $ipAddress2]),
            'firmwareVersion' => 'SF0'.fake()->numberBetween(1, 9).'V_SO0'.fake()->numberBetween(1, 9).'_'.fake()->numberBetween(19, 21).'.'.fake()->numberBetween(0, 9).'.'.fake()->numberBetween(0, 9).'.'.fake()->numberBetween(100, 999),
            'model' => 'SFVUNL_SO0'.fake()->numberBetween(1, 9).'_SFOS '.fake()->numberBetween(19, 21).'.'.fake()->numberBetween(0, 9).'.'.fake()->numberBetween(0, 9).' GA-Build'.fake()->numberBetween(100, 999),
            'status' => [
                'managing' => fake()->randomElement(['approvalPending', 'approved', 'revoked']),
                'reporting' => fake()->randomElement(['approvalPending', 'approved', 'revoked']),
                'connected' => fake()->boolean(90),
                'suspended' => fake()->boolean(5),
            ],
            'stateChangedAt' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d\TH:i:s.000\Z'),
            'capabilities' => fake()->randomElements(
                ['sdwanGroup', 'configImport', 'highAvailability'],
                fake()->numberBetween(1, 2)
            ),
            'geoLocation' => fake()->boolean(50) ? [
                'latitude' => (string) fake()->latitude(),
                'longitude' => (string) fake()->longitude(),
            ] : null,
            'createdBy' => [
                'id' => fake()->uuid(),
                'type' => 'user',
                'name' => fake()->name(),
                'accountType' => 'tenant',
                'accountId' => $tenantId,
            ],
            'createdAt' => $createdAt->format('Y-m-d\TH:i:s.000\Z'),
            'updatedBy' => [
                'id' => fake()->uuid(),
                'type' => 'user',
                'name' => fake()->name(),
                'accountType' => 'partner',
                'accountId' => fake()->uuid(),
            ],
            'updatedAt' => $updatedAt->format('Y-m-d\TH:i:s.000\Z'),
        ];

        return [
            'id' => $firewallId,
            'tenantId' => $tenantId,
            'hostname' => $hostname,
            'rawData' => $rawData,
        ];
    }
}
