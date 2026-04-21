<?php

use App\Models\SCAlert;
use App\Models\SCEndpoint;
use App\Models\SCFirewall;
use App\Models\SCTenant;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Facades\Artisan;

it('runs the demo seeder successfully', function () {
    expect(Artisan::call('db:seed', [
        '--class' => DemoSeeder::class,
        '--no-interaction' => true,
    ]))->toBe(0);

    expect(SCTenant::query()->count())->toBe(5)
        ->and(SCFirewall::query()->whereNotIn('tenantId', SCTenant::query()->select('id'))->count())->toBe(0)
        ->and(SCEndpoint::query()->whereNotIn('tenantId', SCTenant::query()->select('id'))->count())->toBe(0)
        ->and(SCAlert::query()->whereNotIn('tenantId', SCTenant::query()->select('id'))->count())->toBe(0);
});
