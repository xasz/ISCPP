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
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\WebhookLogController;
use App\Http\Middleware\IsHaloIntegrationEnabled;
use App\Http\Middleware\IsNinjaIntegrationEnabled;
use App\Http\Middleware\IsSCAlertsIntegrationEnabled;
use App\Http\Middleware\IsSCEndpointsScheduleEnabled;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Middleware\Google2FAMiddleware;
use App\Http\Middleware\IsSCFirewallsIntegrationEnabled;
use App\Http\Middleware\IsSCTenantHealthscoresScheduleEnabled;

// Define a protected route group middleware that includes 2FA verification
$protectedMiddleware = ['auth', 'verified', Google2FAMiddleware::class];

Route::permanentRedirect ('/', '/dashboard')->name('home');

// Add the 2FA verification route
Route::middleware(['auth'])->group(function () {
    Volt::route('/2fa/verify', '2fa-verify')->name('2fa.verify');
});

// Settings routes - don't require 2FA to access settings
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
    Volt::route('settings/2fa', 'settings.2fa')->name('settings.2fa');
});

// All protected routes below use the common middleware group
Route::middleware($protectedMiddleware)->group(function () {

    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
    });
    
    Route::middleware(IsSCTenantHealthscoresScheduleEnabled::class)->group(function () {
        Route::controller(SCTenantController::class)->group(function () {
            Route::get('/sctenants/healthscores', 'healthscores')->name('sctenants.healthscores');
        });
    });

    
    Route::middleware(IsSCFirewallsIntegrationEnabled::class)->group(function () {
        Route::controller(SCFirewallController::class)->group(function () {
            Route::get('/scfirewalls', 'index')
            ->name('scfirewalls.index');
        });
    });
    
    Route::middleware(IsSCEndpointsScheduleEnabled::class)->group(function () {
        Route::controller(SCEndpointController::class)->group(function () {
            Route::get('/scendpoints', 'index')->name('scendpoints.index');
        });
    });
    

    Route::middleware(IsSCAlertsIntegrationEnabled::class)->group(function () {
        Route::controller(SCAlertController::class)->group(function () {
            Route::get('/scalerts/id/{id}', 'show')->name('scalerts.show');
            Route::get('/scalerts/id/{id}/dispatch', 'dispatchAndShow')->name('scalerts.dispatchAndShow');
            Route::get('/scalerts/autoActions', 'autoActions')->name('scalerts.autoActions');
            Route::get('/scalerts', 'index')->name('scalerts.index');
        });
        
    });
    
    Route::controller(SCBillingController::class)->group(function () {
        Route::get('/scbilling/fetcher', 'fetcher')->name('scbilling.fetcher');
    });
    
    Route::middleware(IsHaloIntegrationEnabled::class)->group(function () {
        Route::controller(SCBillingController::class)->group(function () {
            Route::get('/scbilling/haloPusher', 'haloPusher')->name('scbilling.haloPusher');
            Route::get('/scbillables/dispatchToHaloAndShowTenant/{year}/{month}/{id}', 'dispatchToHaloAndShowTenant')
            ->name('scbillables.dispatchToHaloAndShowTenant');
        });

        Route::controller(SCTenantController::class)->group(function () {
            Route::get('/sctenants/haloMatchingHelper', 'haloMatchingHelper')->name('sctenants.haloMatchingHelper');
        });
    });
    
    Route::middleware(IsNinjaIntegrationEnabled::class)->group(function () {
        Route::controller(SCTenantController::class)->group(function () {
            Route::get('/sctenants/ninjaMatchingHelper', 'ninjaMatchingHelper')->name('sctenants.ninjaMatchingHelper');
        });
    });
    
    Route::controller(SCBillableController::class)->group(function () {
        Route::get('/scbillables/{id}', 'show')->name('scbillables.show');
        Route::get('/scbillables', 'index')->name('scbillables.index');
        
    });
    
    Route::controller(WebhookLogController::class)->group(function () {
        Route::get('/webhookLogs', 'index')->name('webhookLogs.index');
    });
    
    Route::controller(EventController::class)->group(function () {
        Route::get('/events', 'index')->name('events.index');
    });
    
    Route::controller(UserManagementController::class)->group(function () {
        Route::get('/usermanagement', 'index')->name('usermanagement.index');
    });
    
    Route::controller(SettingsController::class)->group(function () {
        Route::get('/generalsettings', 'index')->name('generalsettings.index');
    });

    Route::controller(SCTenantController::class)->group(function () {
        Route::get('/sctenants/{id}', 'show')->name('sctenants.show');
        Route::get('/sctenants', 'index')->name('sctenants.index');
    });
    
});
require __DIR__.'/auth.php';
