<?php

use App\Jobs\RefreshSCAlerts;
use App\Jobs\RefreshSCTenants;
use App\Models\Event;
use App\Models\SCTenant;
use App\Settings\SCServiceSettings;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::job(RefreshSCTenants::class)
    ->everyThirtyMinutes();


Schedule::call(function () {
    Artisan::call('app:queue-refresh-scalerts-jobs-for-all-tenants');
    $settings = resolve(SCServiceSettings::class);
    $settings->lastAlertsSchedule = now();
    $settings->save();
})
->name('queue-refresh-scalerts-jobs-for-all-tenants')
->everyFifteenMinutes()
->when(function () {
    return resolve(SCServiceSettings::class)->alertsScheduleEnabled;
});

Schedule::call(function () {
    Artisan::call('app:queue-refresh-scentpoinds-jobs-for-all-tenants');
    $settings = resolve(SCServiceSettings::class);
    $settings->lastEndpointsSchedule = now();
    $settings->save();
})
->name('queue-refresh-scentpoinds-jobs-for-all-tenants')
->everyThirtyMinutes()
->when(function () {
    return resolve(SCServiceSettings::class)->endpointsScheduleEnabled;
});