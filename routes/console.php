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
->hourly();

// Alerts
Schedule::call(function () {
    if(resolve(SCServiceSettings::class)->alertsScheduleEnabled){
        Artisan::call('app:queue-refresh-scalerts-jobs-for-all-tenants');
        $settings = resolve(SCServiceSettings::class);
        $settings->lastAlertsSchedule = now();
        $settings->save();
    }
})
->name('queue-refresh-scalerts-jobs-for-all-tenants')
->everyThirtyMinutes();

// Endpoints
Schedule::call(function () {
    if(resolve(SCServiceSettings::class)->endpointsScheduleEnabled){
        Artisan::call('app:queue-refresh-scentpoinds-jobs-for-all-tenants');
        $settings = resolve(SCServiceSettings::class);
        $settings->lastEndpointsSchedule = now();
        $settings->save();
    }
})
->name('queue-refresh-scentpoinds-jobs-for-all-tenants')
->hourly();

// Downloads
Schedule::call(function () {
    if(resolve(SCServiceSettings::class)->downloadsScheduleEnabled){
        Artisan::call('app:queue-refresh-downloads-jobs-for-all-tenants');
        $settings = resolve(SCServiceSettings::class);
        $settings->lastDownloadsSchedule = now();
        $settings->save();
    }
})
->name('queue-refresh-downloads-jobs-for-all-tenants')
->hourly();

// Healthscores
Schedule::call(function () {
    if(resolve(SCServiceSettings::class)->healthscoresScheduleEnabled){
        Artisan::call('app:queue-refresh-healthscores-jobs-for-all-tenants');
        $settings = resolve(SCServiceSettings::class);
        $settings->lastHealthscoresSchedule = now();
        $settings->save();
    }
})
->name('queue-refresh-healthscores-jobs-for-all-tenants')
->hourly();