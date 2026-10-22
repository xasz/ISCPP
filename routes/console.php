<?php

use App\Models\Event;
use App\Models\ISCPPEventLog;
use App\Settings\NinjaServiceSettings;
use App\Settings\SCServiceSettings;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    Log::info('hourly schedule started');

    // Refresh all tenants
    Artisan::call('app:queue-sctenants-refresh');

    // Refresh all alerts
    if (resolve(SCServiceSettings::class)->alertsScheduleEnabled) {
        Artisan::call('app:queue-refresh-scalerts-jobs-for-all-tenants');
        $settings = resolve(SCServiceSettings::class);
        $settings->lastAlertsSchedule = now();
        $settings->save();
    }

    // Refresh all endpoints
    if (resolve(SCServiceSettings::class)->endpointsScheduleEnabled) {
        Artisan::call('app:queue-refresh-scendpoints-jobs-for-all-tenants');
        $settings = resolve(SCServiceSettings::class);
        $settings->lastEndpointsSchedule = now();
        $settings->save();
    }

})
    ->name('schedule-hourly')
    ->hourly();

// Prunes ISCPP events past the retention window. Deliveries cascade with their event,
// so only the event log needs pruning.
Schedule::command('model:prune', ['--model' => [ISCPPEventLog::class]])
    ->name('schedule-iscpp-prune')
    ->daily();

Schedule::call(function () {

    Log::info('every six hours schedule started');

    // Refresh all healthscores
    if (resolve(SCServiceSettings::class)->healthscoresScheduleEnabled) {
        Artisan::call('app:queue-refresh-healthscores-jobs-for-all-tenants');
        $settings = resolve(SCServiceSettings::class);
        $settings->lastHealthscoresSchedule = now();
        $settings->save();
    }

    // Refresh all downloads
    if (resolve(SCServiceSettings::class)->downloadsScheduleEnabled) {
        Artisan::call('app:queue-refresh-downloads-jobs-for-all-tenants');
        $settings = resolve(SCServiceSettings::class);
        $settings->lastDownloadsSchedule = now();
        $settings->save();
    }

    // Push all downloads to NinjaOne
    if (resolve(NinjaServiceSettings::class)->autoPushCentralEndpointInstallerUrl) {
        Artisan::call('app:queue-scdownloads-ninja-push');
    }
})
    ->name('schedule-everySixHours')
    ->everySixHours();
