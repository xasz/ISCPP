<?php

use App\Jobs\RefreshSCAlerts;
use App\Jobs\RefreshSCTenants;
use App\Models\Event;
use App\Models\SCTenant;
use App\Settings\SCServiceSettings;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;


    
Schedule::call(function () {
    Log::info('hourly schedule started');

    // Refresh all tenants
    RefreshSCTenants::dispatch();
    
    // Refresh all alerts
    if(resolve(SCServiceSettings::class)->alertsScheduleEnabled){
        Artisan::call('app:queue-refresh-scalerts-jobs-for-all-tenants');
        $settings = resolve(SCServiceSettings::class);
        $settings->lastAlertsSchedule = now();
        $settings->save();
    }

    // Refresh all endpoints
    if(resolve(SCServiceSettings::class)->endpointsScheduleEnabled){
        Artisan::call('app:queue-refresh-scendpoints-jobs-for-all-tenants');
        $settings = resolve(SCServiceSettings::class);
        $settings->lastEndpointsSchedule = now();
        $settings->save();
    }

})
->name('schedule-hourly')
->hourly();
    

Schedule::call(function () {

    Log::info('every six hours schedule started');

    // Refresh all healthscores
    if(resolve(SCServiceSettings::class)->healthscoresScheduleEnabled){
        Artisan::call('app:queue-refresh-healthscores-jobs-for-all-tenants');
        $settings = resolve(SCServiceSettings::class);
        $settings->lastHealthscoresSchedule = now();
        $settings->save();
    }

    // Refresh all downloads
    if(resolve(SCServiceSettings::class)->downloadsScheduleEnabled){
        Artisan::call('app:queue-refresh-downloads-jobs-for-all-tenants');
        $settings = resolve(SCServiceSettings::class);
        $settings->lastDownloadsSchedule = now();
        $settings->save();
    }
        
})
->name('schedule-everySixHours')
->everySixHours();
