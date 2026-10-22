<?php

namespace App\Providers;

use App\Events\ISCPPEvent;
use App\Listeners\RecordISCPPEvent;
use App\Models\SCAlert;
use App\Models\SCEndpoint;
use App\Models\SCFirewall;
use App\Models\SCTenant;
use App\Models\SCTenantHealthscore;
use App\Models\SCTenantHealthscoreThreshold;
use App\Observers\SCAlertAutoAcknowledgeObserver;
use App\Observers\SCAlertISCPPObserver;
use App\Observers\SCAlertObserver;
use App\Observers\SCEndpointISCPPObserver;
use App\Observers\SCFirewallISCPPObserver;
use App\Observers\SCTenantHealthscoreObserver;
use App\Observers\SCTenantHealthscoreThresholdObserver;
use App\Observers\SCTenantISCPPObserver;
use App\Services\ISCPPEventRegistry;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ISCPPEventRegistry::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        require_once app_path('Helpers/queue.php');

        View::addNamespace('layouts', resource_path('views/components/layouts'));

        // The ISCPP recorder listens on the marker interface, so every event class is
        // captured by this one registration. It must be an interface: Laravel resolves
        // listeners via class_implements(), never through a parent class.
        //
        // The method is called record() rather than handle() on purpose. Laravel
        // auto-discovers listeners in app/Listeners by their handle() signature, which
        // would register this a second time on top of the line below and record every
        // event twice.
        Event::listen(ISCPPEvent::class, [RecordISCPPEvent::class, 'record']);

        SCTenant::observe([SCTenantISCPPObserver::class]);
        SCEndpoint::observe([SCEndpointISCPPObserver::class]);
        SCFirewall::observe([SCFirewallISCPPObserver::class]);

        // Threshold state is evaluated from both sides: when scores are stored and when a
        // threshold is created or changed. Both observers call the same evaluator.
        SCTenantHealthscore::observe([SCTenantHealthscoreObserver::class]);
        SCTenantHealthscoreThreshold::observe([SCTenantHealthscoreThresholdObserver::class]);

        // SCAlertISCPPObserver is deliberately first: SCAlertAutoAcknowledgeObserver can
        // throw (SCAlertAcknowledgeAction rethrows), which would otherwise stop the ISCPP
        // event from ever being raised. Its own emission is wrapped so it cannot break
        // the two legacy observers behind it.
        SCAlert::observe([
            SCAlertISCPPObserver::class,
            SCAlertAutoAcknowledgeObserver::class,
            SCAlertObserver::class,
        ]);

        if (config('queue.default') == 'sqlite') {
            $dbPath = database_path('database.sqlite');

            if (! file_exists($dbPath)) {
                file_put_contents($dbPath, '');
                try {
                    $connection = config('database.default');
                    DB::connection($connection)->table('migrations')->where('migration', '0001_01_01_000002_jobs_to_sqlite')->delete();
                    Artisan::call('migrate', [
                        '--force' => true,
                        '--path' => 'database/migrations/'.'0001_01_01_000002_jobs_to_sqlite'.'.php',
                    ]);

                    DB::connection($connection)->table('migrations')->where('migration', '0001_01_01_000001_create_cache_table_to_sqlite')->delete();
                    Artisan::call('migrate', [
                        '--force' => true,
                        '--path' => 'database/migrations/'.'0001_01_01_000001_create_cache_table_to_sqlite'.'.php',
                    ]);
                } catch (\Exception $e) {
                    // Log::warning('We are probably running the deployment for the first time, so we will not run the migrations for the cache and jobs tables.');
                }
            }
        }

        if (config('cache.default') != 'file' && config('cache.default') != 'array') {
            throw new \Exception('Currently only file and array cache are supported. Please set CACHE_STORE=file in your .env file. Current value: '.config('cache.default'));
        }

    }
}
