<?php

use App\Jobs\RefreshSCAlerts;
use App\Jobs\RefreshSCTenants;
use App\Models\Event;
use App\Models\SCTenant;
use Illuminate\Support\Facades\Schedule;

Schedule::job(RefreshSCTenants::class)
    ->everyThirtyMinutes();


Schedule::call(function () {
    $tenants = SCTenant::all();

    Event::logInfo("console", "Dispatching SCAlertRefresh for ". $tenants->count() . " tenants");
    
    $tenants->each(function ($tenant) {
        RefreshSCAlerts::dispatch($tenant);
    });
})->everyThirtyMinutes();
