<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\SCTenant;
use App\Services\ISCPPEvents;
use App\Services\SCService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;

class RefreshSCTenants implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    /**
     * How long a tenant must stay absent from the Sophos response before it is deleted.
     */
    public const REMOVAL_GRACE_HOURS = 6;

    public $tries = 2;

    public function handle(SCService $scService): void
    {
        try {
            Event::log('sctentants', 'info', ['message' => 'SC Tenants refresh initiated']);

            $tenants = $scService->tenants();
            $ids = $tenants->map(function ($tenant) {
                return $tenant['id'];
            });

            Event::logInfo('sctentants', 'Fetched '.$tenants->count().' tenants');

            // Captured before the loop: afterwards it is never zero again. Keeps the
            // very first sync out of the ISCPP event log.
            $isInitialImport = SCTenant::count() === 0;

            ISCPPEvents::muteIf($isInitialImport, function () use ($tenants) {
                $tenants->each(function ($tenant) {
                    SCTenant::updateOrCreate(['id' => $tenant['id']],
                        [
                            'showAs' => $tenant['showAs'] ?? null,
                            'name' => $tenant['name'] ?? null,
                            'dataGeography' => $tenant['dataGeography'] ?? null,
                            'dataRegion' => $tenant['dataRegion'] ?? null,
                            'billingType' => $tenant['billingType'] ?? null,
                            'partnerId' => $tenant['partnerId'] ?? null,
                            'organizationId' => $tenant['organizationId'] ?? null,
                            'apiHost' => $tenant['apiHost'] ?? null,
                            'rawData' => json_encode($tenant),
                            'updated_at' => now(),
                        ]
                    );
                });
            });

            $this->removeStaleTenants($ids);
        } catch (\Exception $e) {
            Event::log('sctentants', 'error', ['message' => $e->getMessage()]);
        }

        Event::log('sctentants', 'info', ['message' => 'Tenants refreshed']);
    }

    /**
     * Delete tenants Sophos no longer returns, but only after a grace period.
     *
     * A partner call that returns no tenants is a 200 rather than an exception, so
     * deleting every absent tenant immediately would wipe the table on a transient
     * fault. Tenants are marked absent first and only removed once they have stayed
     * absent, which also means SCTenantRemoved only fires for real offboardings.
     *
     * The previous bulk delete was replaced by a model delete on purpose: a query
     * builder delete does not fire model events, so no observer would ever see it.
     *
     * @param  Collection<int, string>  $ids
     */
    protected function removeStaleTenants($ids): void
    {
        if ($ids->isEmpty()) {
            Event::logError('sctentants', 'Tenant refresh returned no tenants - skipping delete');

            return;
        }

        SCTenant::whereIn('id', $ids)->whereNotNull('missing_since')->update(['missing_since' => null]);
        SCTenant::whereNotIn('id', $ids)->whereNull('missing_since')->update(['missing_since' => now()]);

        $stale = SCTenant::whereNotIn('id', $ids)
            ->where('missing_since', '<=', now()->subHours(self::REMOVAL_GRACE_HOURS))
            ->get();

        $stale->each->delete();

        Event::log('sctentants', 'info', ['message' => 'Deleted '.$stale->count().' tenants']);
    }
    /*
    public function uniqueId(): string
    {
        return 'refresh-sctenants';
    }*/
}
