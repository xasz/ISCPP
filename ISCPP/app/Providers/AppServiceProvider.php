<?php

namespace App\Providers;

use App\Models\SCAlert;
use App\Observers\SCAlertObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        SCAlert::observe(SCAlertObserver::class);
    }
}
