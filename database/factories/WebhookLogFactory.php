<?php

namespace Database\Factories;

use App\Models\WebhookLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WebhookLog>
 */
class WebhookLogFactory extends Factory
{
    private const ALERT_CATEGORIES = [
        'malware', 'security', 'policy', 'update', 'connectivity', 'protection',
        'surveillance', 'vulnerability', 'threat', 'system', 'firewall', 'compliance',
    ];

    private const SEVERITIES = ['high', 'medium', 'low'];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $alertId = fake()->uuid();
        $tenantId = fake()->uuid();
        $managedAgentId = fake()->uuid();
        $timestamp = fake()->dateTimeBetween('-30 days', 'now');

        // Generate realistic alert payload based on actual Sophos API structure
        $payload = [
            'event' => [
                'id' => $alertId,
                'type' => 'alert',
                'tenantId' => $tenantId,
                'timestamp' => $timestamp->format('Y-m-d\TH:i:s.000\Z'),
                'severity' => self::SEVERITIES[array_rand(self::SEVERITIES, 1)],
                'category' => self::ALERT_CATEGORIES[array_rand(self::ALERT_CATEGORIES, 1)],
                'description' => fake()->paragraph(2),
                'managedAgent' => [
                    'id' => $managedAgentId,
                    'type' => fake()->randomElement(['computer', 'server', 'mobile', 'firewall']),
                    'name' => fake()->domainWord().'.corp.local',
                ],
                'source' => fake()->randomElement(['sophos-central', 'api', 'webhook']),
                'product' => fake()->randomElement(['endpoint', 'firewall', 'email', 'server']),
                'allowedActions' => fake()->randomElements(
                    ['acknowledge', 'isolate', 'block', 'quarantine', 'allow'],
                    fake()->numberBetween(1, 3)
                ),
            ],
            'metadata' => [
                'webhook_delivery_id' => fake()->uuid(),
                'delivery_attempt' => fake()->numberBetween(1, 5),
                'event_count' => fake()->numberBetween(1, 10),
            ],
        ];

        $statusCode = fake()->randomElement([200, 201, 400, 401, 403, 500, 502, 503]);
        $success = in_array($statusCode, [200, 201]);

        return [
            'sc_alert_id' => $alertId,
            'payload' => $payload,
            'url' => 'https://'.fake()->domainName().'/webhook/sophos',
            'statusCode' => $statusCode,
            'response' => $success ? json_encode([
                'status' => 'success',
                'eventId' => fake()->uuid(),
                'message' => 'Webhook processed successfully',
            ]) : json_encode([
                'status' => 'error',
                'message' => fake()->randomElement(['Invalid payload', 'Authentication failed', 'Internal server error']),
                'code' => $statusCode,
            ]),
        ];
    }
}
