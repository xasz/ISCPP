<?php

namespace Database\Factories;

use App\Models\SCEndpoint;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        $endpointId = fake()->unique()->uuid();
        $tenantId = fake()->uuid();
        $types = ['computer', 'server', 'mobile'];
        $healthStatuses = ['good', 'warning', 'critical'];
        $selectedType = $types[array_rand($types, 1)];
        $hostname = fake()->domainWord().'-'.strtoupper(fake()->bothify('??##')).'.corp.local';
        $lastSeen = fake()->dateTimeBetween('-30 days', 'now');
        $tamperProtected = fake()->boolean(75);

        // Generate realistic raw data based on actual Sophos API structure
        $rawData = [
            'id' => $endpointId,
            'tenant' => [
                'id' => $tenantId,
            ],
            'hostname' => $hostname,
            'type' => $selectedType,
            'osVersion' => match ($selectedType) {
                'computer' => 'Windows '.fake()->randomElement(['10', '11']).' Enterprise',
                'server' => 'Windows Server '.fake()->randomElement(['2019', '2022']),
                'mobile' => fake()->randomElement(['iOS 17', 'Android 14']),
            },
            'ipAddresses' => [
                fake()->ipv4(),
            ],
            'macAddresses' => [
                strtoupper(fake()->macAddress()),
            ],
            'lastSeenAt' => $lastSeen->format('Y-m-d\TH:i:s.000\Z'),
            'lastUser' => match (fake()->boolean(70)) {
                true => fake()->userName().'@'.fake()->domainName(),
                false => null,
            },
            'assemblyId' => fake()->uuid(),
            'health' => [
                'overall' => $healthStatuses[array_rand($healthStatuses, 1)],
                'threats' => fake()->randomElement(['good', 'warning', 'critical']),
                'tamperProtection' => fake()->randomElement(['good', 'warning', 'critical']),
                'services' => fake()->randomElement(['good', 'warning', 'critical']),
                'updates' => fake()->randomElement(['good', 'warning', 'critical']),
            ],
            'tamperProtectionEnabled' => $tamperProtected,
            'encryption' => [
                'enabled' => fake()->boolean(60),
                'volumes' => fake()->numberBetween(0, 3),
            ],
            'tags' => fake()->randomElements(['production', 'development', 'testing', 'kiosk', 'vdi'], fake()->numberBetween(0, 3)),
            'createdAt' => fake()->dateTimeBetween('-2 years', '-1 year')->format('Y-m-d\TH:i:s.000\Z'),
            'updatedAt' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d\TH:i:s.000\Z'),
            'createdBy' => [
                'id' => null,
                'type' => null,
                'name' => null,
                'accountType' => 'tenant',
                'accountId' => $tenantId,
            ],
            'updatedBy' => [
                'id' => null,
                'type' => null,
                'name' => null,
                'accountType' => 'tenant',
                'accountId' => $tenantId,
            ],
            'links' => [
                'self' => [
                    'href' => 'https://api.central.sophos.com/endpoints/'.$endpointId,
                    'rel' => 'self',
                ],
            ],
        ];

        return [
            'id' => $endpointId,
            'hostname' => $hostname,
            'tamperProtectionEnabled' => $tamperProtected,
            'lastSeen' => $lastSeen,
            'tenantId' => $tenantId,
            'type' => $selectedType,
            'rawData' => $rawData,
            'healthStatus' => $rawData['health']['overall'],
        ];
    }
}
