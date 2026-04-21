<?php

namespace Database\Seeders;

use App\Models\SCTenant;
use App\Models\SCFirewall;
use App\Models\SCEndpoint;
use App\Models\SCAlert;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        SCTenant::factory()->count(5)->create()->each(function ($tenant) {
            
            SCFirewall::factory()->forTenant($tenant)->count(rand(0, 4))->create()->each(function ($firewall) {
                SCAlert::factory()->forFirewall($firewall)->count(rand(0, 10))->create();
            });

            SCEndpoint::factory()->forTenant($tenant)->count(rand(0, 200))->create()->each(function ($endpoint) {
                SCAlert::factory()->forEndpoint($endpoint)->count(rand(0, 10))->create();
            });
        });
    }
}
