<?php

namespace App\Http\Controllers;

use App\Models\SCTenant;
use App\Services\HaloService;
use App\Services\SCService;
use Illuminate\Support\Facades\Http;

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
                $query->where('name', 'ILIKE', '%' . $validated['filterTenantName'] . '%')
                    ->orWhere('showAs', 'ILIKE', '%' . $validated['filterTenantName'] . '%');
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

    public function show(SCService $service, string $id)
    {
        $sctenant = SCTenant::with('SCTenantDownload')->with('SCTenantHealthscore')->findOrFail($id);
        
        $scalerts = $sctenant->SCAlerts()->orderByDesc('raisedAt')->paginate(50);
        $scbillables = $sctenant->SCBillables()
            ->where('year', '>=', now()->format('Y'))
            ->where('month', '>=', now()->format('m'))
            ->get();
        return view('sctenants.show', 
            compact('sctenant',
                'scalerts', 'scbillables'));
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
