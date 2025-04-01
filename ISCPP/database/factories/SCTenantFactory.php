<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SCTenant>
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
        $regions = ['eu01', 'eu02', 'us01', 'us02', 'us03', 'ca01', 'au01', 'jp01'];
        $types = ['term', 'trial', 'usage'];
        $pid = fake()->uuid();
        $def = [
            'id' => fake()->unique()->uuid(),
            'showAs' => $company,
            'name' => $company,
            'dataGeography' => $geos[array_rand($geos, 1)],
            'dataRegion' => $regions[array_rand($regions,1)],
            'billingType' => $types[array_rand($types,1)],
            'partnerId' => $pid,
            'organizationId' => fake()->uuid(),
            'apiHost' => 'https://fake-api.sophos.com/',            
        ];
        $def['rawData'] = json_encode($def);
        
        return $def;
    }

}
