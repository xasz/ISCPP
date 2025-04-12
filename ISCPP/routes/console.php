<?php

use App\Jobs\RefreshSCAlerts;
use App\Jobs\RefreshSCTenants;
use App\Models\Event;
use App\Models\SCTenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::job(RefreshSCTenants::class)
    ->everyThirtyMinutes();


Schedule::call(function () {
    Artisan::call('app:queue-refresh-scalerts-jobs-for-all-tenants');
})->everyFifteenMinutes();
