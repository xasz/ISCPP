<?php

use Database\Seeders\AnonymizeExistingScDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('existing sc data gets anonymized while classifications stay unchanged', function () {
    $tenantId = (string) Str::uuid();
    $alertId = (string) Str::uuid();
    $endpointId = (string) Str::uuid();
    $firewallId = (string) Str::uuid();

    DB::table('sc_tenants')->insert([
        'id' => $tenantId,
        'showAs' => 'Acme GmbH',
        'name' => 'Acme Holding GmbH',
        'dataGeography' => 'DE',
        'dataRegion' => 'eu01',
        'billingType' => 'term',
        'partnerId' => (string) Str::uuid(),
        'organizationId' => (string) Str::uuid(),
        'apiHost' => 'https://api.example.test',
        'rawData' => json_encode([
            'name' => 'Acme Holding GmbH',
            'showAs' => 'Acme GmbH',
            'id' => $tenantId,
        ]),
        'haloclient_id' => 42,
        'ninjaorg_id' => 7,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('sc_alerts')->insert([
        'id' => $alertId,
        'allowedActions' => json_encode(['acknowledge']),
        'category' => 'malware',
        'description' => 'Detected malware sample',
        'groupKey' => (string) Str::uuid(),
        'managedAgentID' => (string) Str::uuid(),
        'managedAgentName' => 'Server Berlin 01',
        'managedAgentType' => 'endpoint',
        'personID' => (string) Str::uuid(),
        'personName' => 'Max Mustermann',
        'product' => 'central',
        'raisedAt' => now(),
        'severity' => 'high',
        'tenantId' => $tenantId,
        'type' => 'threat-detected',
        'rawData' => json_encode([
            'managedAgentName' => 'Server Berlin 01',
            'personName' => 'Max Mustermann',
            'personID' => (string) Str::uuid(),
        ]),
        'webhook_sent' => 'unplanned',
        'is_acknowledged' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('sc_endpoints')->insert([
        'id' => $endpointId,
        'tenantId' => $tenantId,
        'type' => 'server',
        'hostname' => 'srv-berlin-01',
        'tamperProtectionEnabled' => true,
        'lastSeen' => now(),
        'rawData' => json_encode(['hostname' => 'srv-berlin-01']),
        'healthStatus' => 'good',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('sc_firewalls')->insert([
        'id' => $firewallId,
        'tenantId' => $tenantId,
        'hostname' => 'fw-berlin-01',
        'rawData' => json_encode(['hostname' => 'fw-berlin-01']),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('sc_billable')->insert([
        'month' => 3,
        'year' => 2026,
        'tenantId' => $tenantId,
        'orderLineItemNumber' => 'OLI-1234',
        'productGroup' => 'endpoint',
        'billableQuantity' => 10,
        'orderedQuantity' => 10,
        'actualQuantity' => 10,
        'productCode' => 'PC-123',
        'sku' => 'SKU-1234',
        'productDescription' => 'Endpoint Protection',
        'rawData' => json_encode(['tenantId' => $tenantId]),
        'sent_to_halo' => 'unplanned',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('sc_tenant_downloads')->insert([
        'tenantId' => $tenantId,
        'rawData' => json_encode(['installers' => []]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('sc_tenant_healthscores')->insert([
        'tenantId' => $tenantId,
        'rawData' => json_encode(['endpoint' => ['protection' => []]]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('webhook_log')->insert([
        'sc_alert_id' => $alertId,
        'payload' => json_encode(['personName' => 'Max Mustermann']),
        'url' => 'https://example.test/webhook',
        'statusCode' => 200,
        'response' => 'ok',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $originalCategory = DB::table('sc_alerts')->value('category');
    $originalType = DB::table('sc_alerts')->value('type');
    $originalSeverity = DB::table('sc_alerts')->value('severity');
    $originalProduct = DB::table('sc_alerts')->value('product');

    $this->seed(AnonymizeExistingScDataSeeder::class);

    expect(DB::table('sc_tenants')->count())->toBe(1)
        ->and(DB::table('sc_alerts')->count())->toBe(1)
        ->and(DB::table('sc_endpoints')->count())->toBe(1)
        ->and(DB::table('sc_firewalls')->count())->toBe(1)
        ->and(DB::table('sc_billable')->count())->toBe(1)
        ->and(DB::table('sc_tenant_downloads')->count())->toBe(1)
        ->and(DB::table('sc_tenant_healthscores')->count())->toBe(1)
        ->and(DB::table('webhook_log')->count())->toBe(1);

    $tenant = DB::table('sc_tenants')->first();
    $alert = DB::table('sc_alerts')->first();

    expect($tenant->name)->not()->toBe('Acme Holding GmbH')
        ->and($tenant->showAs)->not()->toBe('Acme GmbH')
        ->and($alert->personName)->not()->toBe('Max Mustermann')
        ->and($alert->managedAgentName)->not()->toBe('Server Berlin 01');

    expect($alert->category)->toBe($originalCategory)
        ->and($alert->type)->toBe($originalType)
        ->and($alert->severity)->toBe($originalSeverity)
        ->and($alert->product)->toBe($originalProduct);

    expect(DB::table('sc_tenant_downloads')->value('tenantId'))->toBe($tenant->id)
        ->and(DB::table('sc_tenant_healthscores')->value('tenantId'))->toBe($tenant->id)
        ->and(DB::table('sc_endpoints')->value('tenantId'))->toBe($tenant->id)
        ->and(DB::table('sc_firewalls')->value('tenantId'))->toBe($tenant->id)
        ->and(DB::table('sc_billable')->value('tenantId'))->toBe($tenant->id)
        ->and(DB::table('webhook_log')->value('sc_alert_id'))->toBe($alert->id);
});
