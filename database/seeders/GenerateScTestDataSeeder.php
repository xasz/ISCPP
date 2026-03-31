<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateScTestDataSeeder extends Seeder
{
    protected int $tenantCount = 192;

    protected int $alertCount = 480;

    protected int $endpointCount = 5974;

    protected int $firewallCount = 8;

    protected int $downloadCount = 191;

    protected int $healthscoreCount = 191;

    protected int $billableCount = 320;

    /**
     * Distinct values sampled from the current real dataset.
     *
     * @var list<string>
     */
    protected array $alertCategories = [
        'updating',
        'malware',
        'appReputation',
        'denc',
        'mobiles',
        'credentialManager',
        'integrations',
        'security',
        'policy',
        'wireless',
        'connectivity',
        'utm',
        'protection',
        'general',
        'pua',
    ];

    /**
     * @var list<string>
     */
    protected array $alertTypes = [
        'Event::Endpoint::CorePuaCleanFailed',
        'Event::Endpoint::Denc::EncryptionSuspendedEvent',
        'Event::Firewall::FirewallAdvancedThreatProtection',
        'Event::Firewall::FirewallVPNTunnelDown',
        'Event::Task::RenewApnsCertificate',
        'Event::Endpoint::MacHealth',
        'Event::Other::WaitingForApproval',
        'Event::Endpoint::Threat::LowRepAppDetected',
        'Event::Mobile::ApnsCertificateRenewed',
        'Event::Other::CredentialAboutToPurgeEvent',
        'Event::Firewall::FirewallAdvancedThreatProtectionDetailed',
        'Event::Other::IntegrationDefaultFailedEvent',
        'Event::Smc::RenewAppleDepToken',
        'Event::Firewall::FirewallVPNTunnelUp',
        'Event::Endpoint::Mobile::Action::Failed',
        'Event::Endpoint::CoreCleanFailed',
        'Event::Other::RegisteredInCentral',
        'Event::Smc::DepRequiresAcceptingTermsOfUse',
        'Event::Other::DeregisteredFromSophosCentral',
        'Event::Endpoint::CloneDetected',
        'Event::Endpoint::Mobile::NowNonCompliant',
        'Event::Endpoint::HeartbeatMissing',
        'Event::Endpoint::Mobile::PlaceholderMissing',
        'Event::Wireless::WifixAccessPoint::Common',
        'Event::Other::FirewallFirmwareUpdateSuccessfullyFinished',
        'Event::Smc::RenewAppleVpp',
        'Event::Firewall::FirewallGatewayUp',
        'Event::Firewall::FirewallSuspendedDisconnected',
        'Event::Endpoint::Enc::DiskNotEncryptedEvent',
        'Event::Endpoint::SavDisabled',
        'Event::Endpoint::CorePuaRestoreFailed',
        'Event::Endpoint::CorePuaRemoteDetection',
    ];

    /**
     * @var list<string>
     */
    protected array $alertSeverities = ['high', 'medium', 'low'];

    /**
     * @var list<string>
     */
    protected array $alertProducts = ['mobile', 'wireless', 'encryption', 'server', 'endpoint', 'other', 'firewall'];

    /**
     * @var list<string>
     */
    protected array $tenantGeographies = ['DE', 'US', 'IE', 'CA', 'AU', 'JP'];

    /**
     * @var list<string>
     */
    protected array $tenantRegions = ['eu01', 'eu02', 'us01', 'us02', 'us03', 'ca01', 'au01', 'jp01'];

    /**
     * @var list<string>
     */
    protected array $tenantBillingTypes = ['term', 'trial', 'usage'];

    /**
     * @var list<string>
     */
    protected array $endpointTypes = ['server', 'computer', 'mobile'];

    /**
     * @var list<string>
     */
    protected array $endpointHealthStatuses = ['good', 'warning', 'critical'];

    /**
     * Seed independent synthetic test data for GUI development.
     */
    public function run(): void
    {
        $this->truncateScTables();

        $tenantIds = $this->seedTenants();
        $alertIds = $this->seedAlerts($tenantIds);
        $this->seedEndpoints($tenantIds);
        $this->seedFirewalls($tenantIds);
        $this->seedDownloads($tenantIds);
        $this->seedHealthscores($tenantIds);
        $this->seedBillables($tenantIds);
        $this->seedWebhookLog($alertIds);
    }

    /**
     * @return list<string>
     */
    protected function seedTenants(): array
    {
        $now = now();
        $tenantIds = [];
        $rows = [];

        for ($i = 1; $i <= $this->tenantCount; $i++) {
            $tenantId = (string) Str::uuid();
            $tenantIds[] = $tenantId;
            $displayName = sprintf('Tenant %03d', $i);
            $partnerId = (string) Str::uuid();
            $organizationId = (string) Str::uuid();

            $payload = [
                'id' => $tenantId,
                'showAs' => $displayName,
                'name' => $displayName,
                'dataGeography' => fake()->randomElement($this->tenantGeographies),
                'dataRegion' => fake()->randomElement($this->tenantRegions),
                'billingType' => fake()->randomElement($this->tenantBillingTypes),
                'partnerId' => $partnerId,
                'organizationId' => $organizationId,
            ];

            $rows[] = [
                'id' => $tenantId,
                'showAs' => $displayName,
                'name' => $displayName,
                'dataGeography' => $payload['dataGeography'],
                'dataRegion' => $payload['dataRegion'],
                'billingType' => $payload['billingType'],
                'partnerId' => $partnerId,
                'organizationId' => $organizationId,
                'apiHost' => 'https://api.example.test',
                'rawData' => json_encode($payload, JSON_UNESCAPED_SLASHES),
                'haloclient_id' => -1,
                'ninjaorg_id' => -1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('sc_tenants')->insert($chunk);
        }

        return $tenantIds;
    }

    /**
     * @param  list<string>  $tenantIds
     * @return list<string>
     */
    protected function seedAlerts(array $tenantIds): array
    {
        $now = now();
        $alertIds = [];
        $rows = [];

        for ($i = 0; $i < $this->alertCount; $i++) {
            $alertId = (string) Str::uuid();
            $alertIds[] = $alertId;
            $tenantId = fake()->randomElement($tenantIds);

            $payload = [
                'id' => $alertId,
                'tenantId' => $tenantId,
                'category' => fake()->randomElement($this->alertCategories),
                'type' => fake()->randomElement($this->alertTypes),
                'severity' => fake()->randomElement($this->alertSeverities),
                'product' => fake()->randomElement($this->alertProducts),
                'personName' => fake()->name(),
                'personID' => (string) Str::uuid(),
                'managedAgentName' => 'Agent-'.strtoupper(Str::random(6)),
                'managedAgentID' => (string) Str::uuid(),
            ];

            $rows[] = [
                'id' => $alertId,
                'allowedActions' => json_encode(['acknowledge', 'investigate']),
                'category' => $payload['category'],
                'description' => fake()->sentence(10),
                'groupKey' => (string) Str::uuid(),
                'managedAgentID' => $payload['managedAgentID'],
                'managedAgentName' => $payload['managedAgentName'],
                'managedAgentType' => fake()->randomElement(['endpoint', 'firewall', 'mobile']),
                'personID' => $payload['personID'],
                'personName' => $payload['personName'],
                'product' => $payload['product'],
                'raisedAt' => fake()->dateTimeBetween('-90 days', 'now'),
                'severity' => $payload['severity'],
                'tenantId' => $tenantId,
                'type' => $payload['type'],
                'rawData' => json_encode($payload, JSON_UNESCAPED_SLASHES),
                'webhook_sent' => fake()->randomElement(['unplanned', 'pending', 'sent']),
                'is_acknowledged' => fake()->boolean(25),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('sc_alerts')->insert($chunk);
        }

        return $alertIds;
    }

    /**
     * @param  list<string>  $tenantIds
     */
    protected function seedEndpoints(array $tenantIds): void
    {
        $now = now();
        $rows = [];

        for ($i = 0; $i < $this->endpointCount; $i++) {
            $endpointId = (string) Str::uuid();
            $tenantId = fake()->randomElement($tenantIds);
            $hostname = sprintf('host-%05d.local', $i + 1);
            $type = fake()->randomElement($this->endpointTypes);
            $healthStatus = fake()->randomElement($this->endpointHealthStatuses);

            $payload = [
                'id' => $endpointId,
                'hostname' => $hostname,
                'type' => $type,
                'healthStatus' => $healthStatus,
                'tenantId' => $tenantId,
            ];

            $rows[] = [
                'id' => $endpointId,
                'tenantId' => $tenantId,
                'type' => $type,
                'hostname' => $hostname,
                'tamperProtectionEnabled' => fake()->boolean(70),
                'lastSeen' => fake()->dateTimeBetween('-30 days', 'now'),
                'rawData' => json_encode($payload, JSON_UNESCAPED_SLASHES),
                'healthStatus' => $healthStatus,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('sc_endpoints')->insert($chunk);
        }
    }

    /**
     * @param  list<string>  $tenantIds
     */
    protected function seedFirewalls(array $tenantIds): void
    {
        $now = now();
        $rows = [];

        for ($i = 0; $i < $this->firewallCount; $i++) {
            $firewallId = (string) Str::uuid();
            $tenantId = fake()->randomElement($tenantIds);
            $hostname = sprintf('fw-%03d.local', $i + 1);

            $rows[] = [
                'id' => $firewallId,
                'tenantId' => $tenantId,
                'hostname' => $hostname,
                'rawData' => json_encode([
                    'id' => $firewallId,
                    'tenantId' => $tenantId,
                    'hostname' => $hostname,
                ], JSON_UNESCAPED_SLASHES),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('sc_firewalls')->insert($rows);
    }

    /**
     * @param  list<string>  $tenantIds
     */
    protected function seedDownloads(array $tenantIds): void
    {
        $now = now();
        $rows = [];
        $selectedTenantIds = collect($tenantIds)->shuffle()->take(min($this->downloadCount, count($tenantIds)))->all();

        foreach ($selectedTenantIds as $tenantId) {
            $rows[] = [
                'tenantId' => $tenantId,
                'rawData' => json_encode([
                    'installers' => [
                        ['platform' => 'windows', 'type' => 'computer', 'downloadUrl' => fake()->url()],
                        ['platform' => 'linux', 'type' => 'server', 'downloadUrl' => fake()->url()],
                        ['platform' => 'macOS', 'type' => 'computer', 'downloadUrl' => fake()->url()],
                    ],
                ], JSON_UNESCAPED_SLASHES),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('sc_tenant_downloads')->insert($rows);
    }

    /**
     * @param  list<string>  $tenantIds
     */
    protected function seedHealthscores(array $tenantIds): void
    {
        $now = now();
        $rows = [];
        $selectedTenantIds = collect($tenantIds)->shuffle()->take(min($this->healthscoreCount, count($tenantIds)))->all();

        foreach ($selectedTenantIds as $tenantId) {
            $score = fn (): int => fake()->numberBetween(40, 100);

            $rows[] = [
                'tenantId' => $tenantId,
                'rawData' => json_encode([
                    'endpoint' => [
                        'protection' => [
                            'computer' => ['score' => $score()],
                            'server' => ['score' => $score()],
                        ],
                        'policy' => [
                            'computer' => ['threat-protection' => ['score' => $score()]],
                            'server' => ['server-threat-protection' => ['score' => $score()]],
                        ],
                        'exclusions' => [
                            'policy' => [
                                'computer' => ['score' => $score()],
                                'server' => ['score' => $score()],
                            ],
                            'global' => ['score' => $score()],
                        ],
                        'tamperProtection' => [
                            'computer' => ['score' => $score()],
                            'server' => ['score' => $score()],
                            'globalDetail' => ['score' => $score()],
                        ],
                        'mdrDataTelemetry' => [
                            'protectionImprovement' => ['score' => $score()],
                        ],
                        'mdrAuthorizedContact' => [
                            'contact' => ['score' => $score()],
                        ],
                    ],
                ], JSON_UNESCAPED_SLASHES),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('sc_tenant_healthscores')->insert($rows);
    }

    /**
     * @param  list<string>  $tenantIds
     */
    protected function seedBillables(array $tenantIds): void
    {
        $now = now();
        $rows = [];

        for ($i = 0; $i < $this->billableCount; $i++) {
            $tenantId = fake()->randomElement($tenantIds);
            $productGroup = fake()->randomElement(['endpoint', 'firewall', 'mobile', 'server', 'wireless']);
            $productCode = strtoupper(fake()->bothify('PC-###'));
            $sku = strtoupper(fake()->bothify('SKU-####'));

            $payload = [
                'tenantId' => $tenantId,
                'productGroup' => $productGroup,
                'productCode' => $productCode,
                'sku' => $sku,
            ];

            $rows[] = [
                'month' => (int) now()->month,
                'year' => (int) now()->year,
                'tenantId' => $tenantId,
                'orderLineItemNumber' => strtoupper(fake()->bothify('OLI-#####')),
                'productGroup' => $productGroup,
                'billableQuantity' => fake()->numberBetween(1, 500),
                'orderedQuantity' => fake()->numberBetween(1, 500),
                'actualQuantity' => fake()->numberBetween(1, 500),
                'productCode' => $productCode,
                'sku' => $sku,
                'productDescription' => ucfirst($productGroup).' protection subscription',
                'rawData' => json_encode($payload, JSON_UNESCAPED_SLASHES),
                'sent_to_halo' => fake()->randomElement(['unplanned', 'planned', 'sent']),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 300) as $chunk) {
            DB::table('sc_billable')->insert($chunk);
        }
    }

    /**
     * @param  list<string>  $alertIds
     */
    protected function seedWebhookLog(array $alertIds): void
    {
        $now = now();
        $rows = [];
        $count = min((int) floor(count($alertIds) * 0.25), 300);

        foreach (collect($alertIds)->shuffle()->take($count)->all() as $alertId) {
            $rows[] = [
                'sc_alert_id' => $alertId,
                'payload' => json_encode(['alertId' => $alertId, 'event' => 'alert.webhook']),
                'url' => 'https://webhook.example.test/sc-alerts',
                'statusCode' => fake()->randomElement([200, 201, 500]),
                'response' => fake()->sentence(6),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            DB::table('webhook_log')->insert($rows);
        }
    }

    protected function truncateScTables(): void
    {
        $this->toggleForeignKeys(false);

        DB::table('webhook_log')->delete();
        DB::table('sc_alerts')->delete();
        DB::table('sc_endpoints')->delete();
        DB::table('sc_firewalls')->delete();
        DB::table('sc_billable')->delete();
        DB::table('sc_tenant_downloads')->delete();
        DB::table('sc_tenant_healthscores')->delete();
        DB::table('sc_tenants')->delete();

        $this->toggleForeignKeys(true);
    }

    protected function toggleForeignKeys(bool $enable): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement($enable ? 'PRAGMA foreign_keys = ON' : 'PRAGMA foreign_keys = OFF');

            return;
        }

        if ($driver === 'mysql') {
            DB::statement($enable ? 'SET FOREIGN_KEY_CHECKS=1' : 'SET FOREIGN_KEY_CHECKS=0');
        }
    }
}
