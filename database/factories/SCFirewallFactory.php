<?php

namespace Database\Factories;

use App\Models\SCFirewall;
use App\Models\SCTenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SCFirewallFactory extends Factory
{
    /*
{
    "id": "1c84f88e-2254-48f8-a1df-3dca90bfe646",
    "cluster": null,
    "tenant": {
        "id": "a2b050e8-9c01-4fd1-aed6-5df3811919b1"
    },
    "serialNumber": "X11103QPX83YHA2",
    "group": null,
    "hostname": "ZVSSLFW1",
    "name": "X11103QPX83YHA2",
    "firmwareVersion": "XGS116_XN01_19.5.3.652",
    "externalIpv4Addresses": [
        "87.138.199.170"
    ],
    "model": "XGS116_XN01_SFOS 19.5.3 MR-3-Build652",
    "status": {
        "managingStatus": "approvedByCustomer",
        "reportingStatus": "approvedByCustomer",
        "connected": false,
        "suspended": true
    },
    "stateChangedAt": "2024-08-29T08:54:00.19Z",
    "capabilities": [
        "highAvailability",
        "configImport",
        "sdwanGroup",
        "sdwanMultiGroup"
    ],
    "createdBy": {
        "id": null,
        "type": null,
        "name": null,
        "accountType": "tenant",
        "accountId": "a2b050e8-9c01-4fd1-aed6-5df3811919b1"
    },
    "createdAt": "2021-12-08T07:50:22.852Z",
    "updatedBy": {
        "id": null,
        "type": null,
        "name": null,
        "accountType": "tenant",
        "accountId": "a2b050e8-9c01-4fd1-aed6-5df3811919b1"
    },
    "updatedAt": "2024-09-28T08:58:55.996Z",
    "geoLocation": null
}
    
     */
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
