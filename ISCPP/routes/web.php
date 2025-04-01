<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SCAlertController;
use App\Http\Controllers\SCBillingController;
use App\Http\Controllers\SCTenantController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SCFirewallController;
use App\Http\Controllers\SCEndpointController;
use App\Http\Controllers\SCBillableController;
use App\Http\Controllers\WebhookLogController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::permanentRedirect ('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified'])->controller(DashboardController::class)->group(function () {
    Route::get('/dashboard', 'index')->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

Route::middleware(['auth', 'verified'])->controller(SCTenantController::class)->group(function () {
    Route::get('/sctenants/{id}', 'show')->name('sctenants.show');
    Route::get('/sctenants', 'index')->name('sctenants.index');
});

Route::middleware(['auth', 'verified'])->controller(SCFirewallController::class)->group(function () {
    Route::get('/scfirewalls', 'index')->name('scfirewalls.index');
});

Route::middleware(['auth', 'verified'])->controller(SCEndpointController::class)->group(function () {
    Route::get('/scendpoints', 'index')->name('scendpoints.index');
});

Route::middleware(['auth', 'verified'])->controller(SCAlertController::class)->group(function () {
    Route::get('/scalerts/{id}', 'show')->name('scalerts.show');
    Route::get('/scalerts/{id}/dispatch', 'dispatchAndShow')->name('scalerts.dispatchAndShow');
    Route::get('/scalerts', 'index')->name('scalerts.index');
});

Route::middleware(['auth', 'verified'])->controller(SCBillingController::class)->group(function () {
    Route::get('/scbilling/fetcher', 'fetcher')->name('scbilling.fetcher');
    Route::get('/scbilling/haloPusher', 'haloPusher')->name('scbilling.haloPusher');

});

Route::middleware(['auth', 'verified'])->controller(SCBillableController::class)->group(function () {
    Route::get('/scbillables/{id}', 'show')->name('scbillables.show');
    Route::get('/scbillables', 'index')->name('scbillables.index');
    Route::get('/scbillables/dispatchToHaloAndShowTenant/{year}/{month}/{id}', 'dispatchToHaloAndShowTenant')->name('scbillables.dispatchToHaloAndShowTenant');
});

Route::middleware(['auth', 'verified'])->controller(WebhookLogController::class)->group(function () {
    Route::get('/webhookLogs', 'index')->name('webhookLogs.index');
});

Route::middleware(['auth', 'verified'])->controller(EventController::class)->group(function () {
    Route::get('/events', 'index')->name('events.index');
});

Route::middleware(['auth', 'verified'])->controller(SettingsController::class)->group(function () {
    Route::get('/generalsettings', 'index')->name('generalsettings.index');
});

require __DIR__.'/auth.php';
