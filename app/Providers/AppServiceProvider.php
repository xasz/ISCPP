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
        require_once app_path('Helpers/queue.php');
        
        SCAlert::observe(SCAlertObserver::class);

        if(config('queue.default') == 'sqlite'){
            $dbPath = database_path('database.sqlite');

            if (!file_exists($dbPath)) {
                file_put_contents($dbPath, '');
                try{
                    $connection = config('database.default');
                    DB::connection($connection)->table('migrations')->where('migration', "0001_01_01_000002_jobs_to_sqlite")->delete();
                    Artisan::call('migrate', [
                        '--force' => true,
                        '--path' => 'database/migrations/' . "0001_01_01_000002_jobs_to_sqlite" . '.php',
                    ]);
                    
                    DB::connection($connection)->table('migrations')->where('migration', '0001_01_01_000001_create_cache_table_to_sqlite')->delete();
                    Artisan::call('migrate', [
                        '--force' => true,
                        '--path' => 'database/migrations/' . "0001_01_01_000001_create_cache_table_to_sqlite" . '.php',
                    ]);
                } catch (\Exception $e) {
                    //Log::warning('We are probably running the deployment for the first time, so we will not run the migrations for the cache and jobs tables.');
                }
            }
        }

        
        if(config('cache.default') != 'file'){
            throw new \Exception('Currently only file cache is supported. Please set CACHE_STORE=file in your .env file.');
        }
        
    }
}
