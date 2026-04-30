<?php

namespace Database\Factories;

use App\Models\SCFirewall;
use App\Models\SCTenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SCFirewallFactory extends Factory
{
    
    public function definition(): array
    {

        $rawData = [
            'id' => (string) Str::uuid(),
            'tenant' => [
                'id' => (string) Str::uuid(),
            ],
            'serialNumber' => fake()->bothify('X##########'),
            "group" => null,
            'hostname' => fake()->domainWord(),
            'name' => fake()->bothify('X##########'),
            'firmwareVersion' => fake()->semver(),
            'externalIpv4Addresses' => [fake()->ipv4()],
            'model' => fake()->randomElement(['XGS117_XN01_SFOS', 'XGS127_XN01_SFOS', 'XGS137_XN01_SFOS']) . ' ' . fake()->semver(),
            'status' => [
                'managingStatus' => fake()->randomElement(['approvedByCustomer', 'pending', 'rejected']),
                'reportingStatus' => fake()->randomElement(['approvedByCustomer', 'pending', 'rejected']),
                'connected' => fake()->boolean(),
                'suspended' => fake()->boolean(),
            ],
            'stateChangedAt' => fake()->dateTimeThisYear()->format('Y-m-d\TH:i:s.v\Z'),
            'capabilities' => fake()->randomElements(['highAvailability', 'configImport', 'sdwanGroup', 'sdwanMultiGroup'], 2),
            'createdBy' => [
                'id' => null,
                'type' => null,
                'name' => null,
                'accountType' => 'tenant',
                'accountId' => (string) Str::uuid(),
            ],
            'createdAt' => fake()->dateTimeThisYear()->format('Y-m-d\TH:i:s.v\Z'),
            'updatedBy' => [
                'id' => null,
                'type' => null,
                'name' => null,
                'accountType' => 'tenant',
                'accountId' => (string) Str::uuid(),
            ],
            'updatedAt' => fake()->dateTimeThisYear()->format('Y-m-d\TH:i:s.v\Z'),
        ];
        return [
            'id' => $rawData['id'],
            'tenantId' => $rawData['tenant']['id'],
            'hostname' => $rawData['hostname'],
            'rawData' => $rawData,
        ];
    }

    function forTenant(SCTenant $tenant)
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
