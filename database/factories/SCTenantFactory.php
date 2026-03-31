<?php

namespace Database\Factories;

use App\Models\SCTenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SCTenant>
 */
class SCTenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $company = fake()->company();
        $geos = ['US', 'IE', 'DE', 'CA', 'AU', 'JP'];
        $geo = $geos[array_rand($geos)];
        $regions = match ($geo) {
            'US' => ['us01', 'us02', 'us03'],
            'IE' => ['eu01'],
            'DE' => ['eu02'],
            'CA' => ['ca01'],
            'AU' => ['au01'],
            'JP' => ['jp01'],
        };
        $region = $regions[array_rand($regions)];
        $billingTypes = ['term', 'trial', 'usage'];

        $tenantId = fake()->unique()->uuid();
        $partnerId = fake()->uuid();
        $organizationId = fake()->uuid();
        $billingType = $billingTypes[array_rand($billingTypes)];

        // Build realistic raw data matching actual Sophos API structure
        $rawData = [
            'id' => $tenantId,
            'showAs' => $company.' - '.fake()->postcode(),
            'name' => $company,
            'dataGeography' => $geo,
            'dataRegion' => $region,
            'billingType' => $billingType,
            'partner' => [
                'id' => $partnerId,
            ],
            'organization' => [
                'id' => $organizationId,
            ],
            'apiHost' => 'https://api-'.$region.'.central.sophos.com',
            'products' => [
                ['code' => 'AP6-840-SUP-MSP'],
                ['code' => 'CMS-MSP'],
                ['code' => 'CDE-MSP'],
                ['code' => 'CEMA-MSP'],
            ],
            'status' => 'active',
            'contact' => [
                'firstName' => fake()->firstName(),
                'lastName' => fake()->lastName(),
                'email' => fake()->email(),
                'phone' => fake()->phoneNumber(),
                'address' => [
                    'address1' => fake()->streetAddress(),
                    'address2' => '',
                    'city' => fake()->city(),
                    'state' => strtoupper(fake()->bothify('??')),
                    'countryCode' => $geo,
                    'postalCode' => fake()->postcode(),
                ],
            ],
        ];

        return [
            'id' => $tenantId,
            'showAs' => $rawData['showAs'],
            'name' => $company,
            'dataGeography' => $geo,
            'dataRegion' => $region,
            'billingType' => $billingType,
            'partnerId' => $partnerId,
            'organizationId' => $organizationId,
            'apiHost' => $rawData['apiHost'],
            'rawData' => json_encode($rawData),
        ];
    }
}
