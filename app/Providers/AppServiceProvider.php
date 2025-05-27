<?php

namespace App\Providers;

use App\Models\SCAlert;
use App\Observers\SCAlertObserver;
use Illuminate\Support\Facades\Artisan;
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

        $dbPath = database_path('database.sqlite');

        if (!file_exists($dbPath)) {
            file_put_contents($dbPath, '');

            Artisan::call('migrate', [
                '--force' => true,
                '--path' => 'database/migrations/2025_05_27_094735_jobs_to_sqlite.php',
            ]);
        }
    }
}
