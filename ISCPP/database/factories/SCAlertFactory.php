<?php

namespace Database\Factories;

use App\Models\SCAlert;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SCAlert>
 */
class SCAlertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {   
        $catergories = [
            'azure', 'adSync', 'applicationControl', 'appReputation', 'blockListed', 'connectivity', 'cwg', 'denc', 'downloadReputation', 'endpointFirewall', 'fenc', 'forensicSnapshot', 'general', 'isolation', 'malware', 'mtr', 'mobiles', 'policy', 'protection', 'pua', 'runtimeDetections', 'security', 'smc', 'systemHealth', 'uav', 'uncategorized', 'updating', 'utm', 'virt', 'wireless', 'xgEmail', 'ztnaAuthentication', 'ztnaGateway', 'ztnaResource'
        ];

        $agentType = [
            'endpoint', 'firewall', 'email', 'server', 'mobile', 'web', 'cloud', 'network', 'iot', 'vpn', 'authentication', 'gateway', 'resource'
        ];

        $products = [
            'central', 'central-firewall', 'central-email', 'central-server', 'central-mobile', 'central-web', 'central-cloud', 'central-network', 'central-iot', 'central-vpn', 'central-authentication', 'central-gateway', 'central-resource'
        ];

        $serverity = [
            'high', 'medium', 'low'
        ];

        $def = [
            'id' => fake()->unique()->uuid(),    
            'allowedActions' => ['action1', 'action2'],
            'category' => $catergories[array_rand($catergories, 1)],
            'description' => fake()->sentence(),
            'groupKey' => fake()->unique()->uuid(),
            'managedAgentID' => fake()->unique()->uuid(),
            'managedAgentName' => fake()->name(),
            'managedAgentType' => $agentType[array_rand($agentType, 1)],
            'personID' => fake()->unique()->uuid(),
            'personName' => fake()->name(),
            'product' => $products[array_rand($products, 1)],
            'raisedAt' => fake()->dateTimeThisYear(),
            'severity' => $serverity[array_rand($serverity, 1)],
            'type' => fake()->word(),        
        ];
        $def['rawData'] = json_encode($def);
        
        return $def;
    }



}
