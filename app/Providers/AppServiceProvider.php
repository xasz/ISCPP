<?php

namespace App\Providers;

use App\Models\SCAlert;
use App\Observers\SCAlertObserver;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
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
            try{
                DB::table('migrations')->where('migration', "0001_01_01_000002_jobs_to_sqlite")->delete();
                Artisan::call('migrate', [
                    '--force' => true,
                    '--path' => 'database/migrations/' . "0001_01_01_000002_jobs_to_sqlite" . '.php',
                ]);
                
                DB::table('migrations')->where('migration', '0001_01_01_000001_create_cache_table_to_sqlite')->delete();
                Artisan::call('migrate', [
                    '--force' => true,
                    '--path' => 'database/migrations/' . "0001_01_01_000001_create_cache_table_to_sqlite" . '.php',
                ]);
            } catch (\Exception $e) {
                Log::warning('We are probably running the deployment for the first time, so we will not run the migrations for the cache and jobs tables.');
            }
        }
    }
}
