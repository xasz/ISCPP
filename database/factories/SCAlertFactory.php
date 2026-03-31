<?php

namespace Database\Factories;

use App\Models\SCAlert;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SCAlert>
 */
class SCAlertFactory extends Factory
{
    private const CATEGORIES = [
        'malware', 'security', 'policy', 'update', 'connectivity', 'protection',
        'threat', 'system', 'firewall', 'compliance', 'isolation',
    ];

    private const SEVERITIES = ['high', 'medium', 'low'];

    private const PRODUCTS = [
        'endpoint', 'firewall', 'email', 'server', 'mobile',
    ];

    private const AGENT_TYPES = [
        'computer', 'server', 'mobile', 'utm', 'ec2', 'azure',
    ];

    private const EVENT_TYPES = [
        'Event::Virus',
        'Event::Spyware',
        'Event::PUA',
        'Event::Policy',
        'Event::Firewall',
        'Event::Firewall::FirewallREDTunnelDown',
        'Event::UpdateFailed',
        'Event::CloudFailed',
        'Event::ServiceFailed',
        'Event::TamperAlert',
        'Event::Scan',
        'Event::Isolation',
        'Event::Threat',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $alertId = fake()->unique()->uuid();
        $tenantId = fake()->uuid();
        $managedAgentId = fake()->uuid();
        $category = self::CATEGORIES[array_rand(self::CATEGORIES, 1)];
        $severity = self::SEVERITIES[array_rand(self::SEVERITIES, 1)];
        $product = self::PRODUCTS[array_rand(self::PRODUCTS, 1)];
        $type = self::EVENT_TYPES[array_rand(self::EVENT_TYPES, 1)];
        $raisedAt = fake()->dateTimeBetween('-90 days', 'now');

        // Generate realistic raw data based on actual Sophos API structure
        $rawData = [
            'id' => $alertId,
            'tenant' => [
                'id' => $tenantId,
            ],
            'type' => $type,
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(2),
            'category' => $category,
            'severity' => $severity,
            'product' => $product,
            'raisedAt' => $raisedAt->format('Y-m-d\TH:i:s.000\Z'),
            'managedAgent' => [
                'id' => $managedAgentId,
                'type' => fake()->randomElement(['computer', 'server', 'mobile', 'firewall']),
                'name' => fake()->domainWord().'.corp.local',
            ],
            'owner' => match (fake()->boolean(60)) {
                true => [
                    'id' => fake()->uuid(),
                    'name' => fake()->name(),
                    'email' => fake()->email(),
                ],
                false => null,
            },
            'comment' => match (fake()->boolean(30)) {
                true => fake()->sentence(),
                false => null,
            },
            'allowedActions' => fake()->randomElements(
                ['acknowledge', 'isolate', 'block', 'quarantine', 'allow'],
                fake()->numberBetween(1, 3)
            ),
            'isAcknowledged' => fake()->boolean(30),
            'acknowledgment' => match (fake()->boolean(30)) {
                true => [
                    'acknowledgedAt' => fake()->dateTimeBetween($raisedAt, 'now')->format('Y-m-d\TH:i:s.000\Z'),
                    'acknowledgedBy' => [
                        'id' => fake()->uuid(),
                        'name' => fake()->name(),
                        'email' => fake()->email(),
                    ],
                ],
                false => null,
            },
            'createdAt' => $raisedAt->format('Y-m-d\TH:i:s.000\Z'),
            'updatedAt' => fake()->dateTimeBetween($raisedAt, 'now')->format('Y-m-d\TH:i:s.000\Z'),
            'links' => [
                'self' => [
                    'href' => 'https://api.central.sophos.com/alerts/'.$alertId,
                    'rel' => 'self',
                ],
            ],
        ];

        return [
            'id' => $alertId,
            'allowedActions' => $rawData['allowedActions'],
            'category' => $category,
            'description' => $rawData['description'],
            'groupKey' => fake()->uuid(),
            'managedAgentID' => $managedAgentId,
            'managedAgentName' => $rawData['managedAgent']['name'],
            'managedAgentType' => $rawData['managedAgent']['type'],
            'personID' => $rawData['owner']['id'] ?? fake()->uuid(),
            'personName' => $rawData['owner']['name'] ?? 'Unknown',
            'product' => $product,
            'raisedAt' => $raisedAt,
            'severity' => $severity,
            'tenantId' => $tenantId,
            'type' => $type,
            'rawData' => json_encode($rawData),
            'webhook_sent' => null,
            'is_acknowledged' => $rawData['isAcknowledged'],
        ];
    }
}
