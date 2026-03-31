<?php

use Database\Seeders\GenerateScTestDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

class SmallGenerateScTestDataSeeder extends GenerateScTestDataSeeder
{
    protected int $tenantCount = 12;

    protected int $alertCount = 36;

    protected int $endpointCount = 90;

    protected int $firewallCount = 3;

    protected int $downloadCount = 10;

    protected int $healthscoreCount = 10;

    protected int $billableCount = 20;
}

test('independent synthetic seeder builds a complete related gui dataset', function () {
    (new SmallGenerateScTestDataSeeder)->run();

    expect(DB::table('sc_tenants')->count())->toBe(12)
        ->and(DB::table('sc_alerts')->count())->toBe(36)
        ->and(DB::table('sc_endpoints')->count())->toBe(90)
        ->and(DB::table('sc_firewalls')->count())->toBe(3)
        ->and(DB::table('sc_tenant_downloads')->count())->toBe(10)
        ->and(DB::table('sc_tenant_healthscores')->count())->toBe(10)
        ->and(DB::table('sc_billable')->count())->toBe(20);

    $tenantIds = DB::table('sc_tenants')->pluck('id')->all();

    expect(DB::table('sc_alerts')->whereNotIn('tenantId', $tenantIds)->count())->toBe(0)
        ->and(DB::table('sc_endpoints')->whereNotIn('tenantId', $tenantIds)->count())->toBe(0)
        ->and(DB::table('sc_firewalls')->whereNotIn('tenantId', $tenantIds)->count())->toBe(0)
        ->and(DB::table('sc_tenant_downloads')->whereNotIn('tenantId', $tenantIds)->count())->toBe(0)
        ->and(DB::table('sc_tenant_healthscores')->whereNotIn('tenantId', $tenantIds)->count())->toBe(0)
        ->and(DB::table('sc_billable')->whereNotIn('tenantId', $tenantIds)->count())->toBe(0);

    $invalidSeverities = DB::table('sc_alerts')
        ->whereNotIn('severity', ['high', 'medium', 'low'])
        ->count();

    expect($invalidSeverities)->toBe(0);
});
