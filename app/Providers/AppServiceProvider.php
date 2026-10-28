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
use App\Models\User;
use App\Observers\SCAlertAutoAcknowledgeObserver;
use App\Observers\SCAlertISCPPObserver;
use App\Observers\SCAlertObserver;
use App\Observers\SCEndpointISCPPObserver;
use App\Observers\SCFirewallISCPPObserver;
use App\Observers\SCTenantHealthscoreObserver;
use App\Observers\SCTenantHealthscoreThresholdObserver;
use App\Observers\SCTenantISCPPObserver;
use App\Services\ISCPPEventRegistry;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
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
     * Build every URL from APP_URL in production instead of from the request.
     *
     * ISCPP usually runs behind one or more reverse proxies, which pass on an internal
     * Host header such as "localhost:8880". Proxies tend to rewrite Location headers,
     * but not the redirect target inside a Livewire JSON response, so a save would
     * otherwise send the browser to the internal address.
     */
    public function forceApplicationUrl(): void
    {
        $appUrl = (string) config('app.url');

        if (! $this->app->isProduction() || $appUrl === '') {
            return;
        }

        URL::forceRootUrl($appUrl);

        if (str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->forceApplicationUrl();

        View::addNamespace('layouts', resource_path('views/components/layouts'));

        // Two abilities rather than one role check, so the areas can be split between
        // roles later without touching routes and components.
        Gate::define('manage-users', fn (User $user) => $user->isSuperAdmin());
        Gate::define('manage-settings', fn (User $user) => $user->isSuperAdmin());

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
    }
}
