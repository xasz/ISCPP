<?php

use App\Jobs\RefreshSCAlerts;
use App\Jobs\RefreshSCTenants;
use App\Models\Event;
use App\Models\SCTenant;
use App\Settings\SCServiceSettings;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Tenants
Schedule::job(RefreshSCTenants::class)
    ->everyThirtyMinutes();


// Alerts
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

// Endpoints
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

// Downloads
Schedule::call(function () {
    Artisan::call('app:queue-refresh-downloads-jobs-for-all-tenants');
    $settings = resolve(SCServiceSettings::class);
    $settings->lastDownloadsSchedule = now();
    $settings->save();
})
->name('queue-refresh-downloads-jobs-for-all-tenants')
->hourly()
->when(function () {
    return resolve(SCServiceSettings::class)->downloadsScheduleEnabled;
});

// Healthscores
Schedule::call(function () {
    Artisan::call('app:queue-refresh-healthscores-jobs-for-all-tenants');
    $settings = resolve(SCServiceSettings::class);
    $settings->lastHealthscoresSchedule = now();
    $settings->save();
})
->name('queue-refresh-healthscores-jobs-for-all-tenants')
->everyThirtyMinutes()
->when(function () {
    return resolve(SCServiceSettings::class)->healthscoresScheduleEnabled;
});