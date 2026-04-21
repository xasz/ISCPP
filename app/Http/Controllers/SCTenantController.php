<?php

namespace App\Http\Controllers;

use App\Models\SCTenant;
use App\Services\HaloService;
use App\Services\SCService;

class SCTenantController extends Controller
{
    public function index(HaloService $haloService)
    {

        $validated = collect(request()->validate([
            'filterTenantName' => 'nullable|string|max:255',
            'filterTenantType' => 'nullable|string|in:usage,trail,term',
        ]));

        $sctenants = SCTenant::orderBy('name', 'desc')
            ->when($validated->has('filterTenantName'), function ($query) use ($validated) {
                $query->where('name', 'ILIKE', '%'.$validated['filterTenantName'].'%')
                    ->orWhere('showAs', 'ILIKE', '%'.$validated['filterTenantName'].'%');
            })
            ->when($validated->has('filterTenantType'), function ($query) use ($validated) {
                $query->where('billingType', 'ILIKE', $validated['filterTenantType']);
            })
            ->paginate(50);

        $tenantsCount = [
            'all' => SCTenant::count(),
            'usage' => SCTenant::where('billingType', 'usage')->count(),
            'trail' => SCTenant::where('billingType', 'trail')->count(),
            'term' => SCTenant::where('billingType', 'term')->count(),
        ];

        return view('sctenants.index', compact('sctenants', 'tenantsCount'));
    }

    public function tenantDetails(SCService $service, SCTenant $sctenant)
    {
        $sctenant->load('SCTenantDownload');

        return view('sctenants.show.details', compact('sctenant'));
    }

    public function tenantAlerts(SCService $service, SCTenant $sctenant)
    {
        $scalerts = $sctenant->SCAlerts()->orderByDesc('raisedAt')->paginate(50);

        return view('sctenants.show.alerts', compact('sctenant', 'scalerts'));
    }

    public function tenantBillables(SCService $service, SCTenant $sctenant)
    {
        $scbillables = $sctenant->SCBillables()
            ->where('year', '>=', now()->format('Y'))
            ->where('month', '>=', now()->format('m'))
            ->get();

        return view('sctenants.show.billables', compact('sctenant', 'scbillables'));
    }

    public function tenantHealthscore(SCService $service, SCTenant $sctenant)
    {
        $healthscore = $sctenant->SCTenantHealthscore()->first();

        return view('sctenants.show.healthscore', compact('sctenant', 'healthscore'));
    }

    public function tenantEndpoints(SCService $service, SCTenant $sctenant)
    {
        $endpoints = $sctenant->SCEndpoints()->orderByDesc('lastSeen')->paginate(50);

        return view('sctenants.show.endpoints', compact('sctenant', 'endpoints'));
    }

    public function tenantFirewalls(SCService $service, SCTenant $sctenant)
    {
        $firewalls = $sctenant->SCFirewalls()->paginate(50);

        return view('sctenants.show.firewalls', compact('sctenant', 'firewalls'));
    }

    public function tenantISCPPSettings(SCService $service, SCTenant $sctenant)
    {
        return view('sctenants.show.iscpp-settings', compact('sctenant'));
    }

    public function healthscores()
    {
        $sctenants = SCTenant::orderBy('name', 'desc')
            ->paginate(50);

        return view('sctenants.healthscores', compact('sctenants'));
    }

    public function haloMatchingHelper()
    {
        return view('sctenants.haloMatchingHelper',
            [
                'SCTenants' => SCTenant::where('haloclient_id', '<=', 0)->get(),
            ]
        );
    }

    public function ninjaMatchingHelper()
    {
        return view('sctenants.ninjaMatchingHelper',
            [
                'SCTenants' => SCTenant::where('ninjaorg_id', '<=', 0)->get(),
            ]
        );
    }
}
