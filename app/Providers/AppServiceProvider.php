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

        if(env('DB_CONNECTION') != 'sqlite'){
            throw new \Exception('The application is not configured to use SQLite. Please set DB_CONNECTION=sqlite in your .env file.');
        }
        
        if(env('QUEUE_CONNECTION') != 'file'){
            throw new \Exception('The application is not configured to use file for queues. Please set QUEUE_CONNECTION=file in your .env file.');
        }
        
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
                //Log::warning('We are probably running the deployment for the first time, so we will not run the migrations for the cache and jobs tables.');
            }
        }
    }
}
