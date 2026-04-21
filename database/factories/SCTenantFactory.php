<?php

namespace Database\Factories;

use App\Models\SCTenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SCTenant>
 */
class SCTenantFactory extends Factory
{
    /**
     * Representative tenant profiles captured from the live database.
     *
     * @var list<array{
     *     dataGeography: ?string,
     *     dataRegion: ?string,
     *     billingType: string,
     *     apiHost: ?string,
     *     status: ?string,
     *     products: list<string>
     * }>
     */

    private const REAL_GEO = [
        ['dataGeography' => 'DE', 'dataRegion' => 'eu02', 'apiHost' => 'https://api-eu02.central.sophos.com'],
        ['dataGeography' => 'IE', 'dataRegion' => 'eu01', 'apiHost' => 'https://api-eu01.central.sophos.com'],
        ['dataGeography' => 'US', 'dataRegion' => 'us03', 'apiHost' => 'https://api-us03.central.sophos.com'],
    ];
    private const REAL_BILLING_TYPES = ['trial', 'usage', 'term'];

    private const REAL_STATUS = ['active', 'suspended'];

    private const REAL_PRODUCTS = [
        'CMS-MSP', 
        'SVRCIXAMTR-ADV-MSP', 
        'CIXAMTR-ADV-MSP', 
        'CDE-MSP', 
        'CW7-SUP-MSP', 
        'CEMA-MSP', 
        'CPHISH-MSP', 
        'NDR-MSP',
        'SVRCLOUDADV-MSP',
        'CIXA-MSP',
        'CMA-MSP',
        'xg_email_std',
        'CEMA-PE-ADDON-MSP',
        'CPHISH-MSP',
    ];

    public function definition(): array
    {
        $id = (string) Str::uuid();
        $name = fake()->company();
        $showAs = $name;
        if(fake()->boolean(20)) {
            $showAs .= ' ' . fake()->companySuffix();
        }

        $profile = fake()->randomElement(self::REAL_GEO) + [
            'billingType' => fake()->randomElement(self::REAL_BILLING_TYPES),
            'status' => fake()->randomElement(self::REAL_STATUS),
            'products' => fake()->randomElements(self::REAL_PRODUCTS, fake()->numberBetween(1, 10)),
        
        ];
        $contact = [
            'firstName' => fake()->firstName(),
            'lastName' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'address' => [
                'address1' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->bothify('??'),
                'countryCode' => $profile['dataGeography'] ?? fake()->countryCode(),
                'postalCode' => fake()->postcode(),
            ],
        ];

        $rawData = [
            'id' => $id,
            'showAs' => $showAs,
            'name' => $name,
            'dataGeography' => $profile['dataGeography'],
            'dataRegion' => $profile['dataRegion'],
            'billingType' => $profile['billingType'],
            'partner' => [
                'id' => null,
            ],
            'organization' => [
                'id' => null,
            ],
            'apiHost' => $profile['apiHost'],
            'products' => array_map(
                static fn (string $code): array => ['code' => $code],
                $profile['products'],
            ),
            'status' => $profile['status'],
            'contact' => $contact,
        ];

        return [
            'id' => $rawData['id'],
            'showAs' => $rawData['showAs'],
            'name' => $rawData['name'],
            'dataGeography' => $rawData['dataGeography'],
            'dataRegion' => $rawData['dataRegion'],
            'billingType' => $rawData['billingType'],
            'partnerId' => $rawData['partner']['id'],
            'organizationId' => $rawData['organization']['id'],
            'apiHost' => $rawData['apiHost'],
            'rawData' => json_encode($rawData, JSON_UNESCAPED_SLASHES),
            'haloclient_id' => -1,
            'ninjaorg_id' => -1,
        ];
    }
}
