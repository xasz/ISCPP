<?php

namespace App\Http\Controllers;

use App\Models\SCTenant;
use App\Services\HaloService;

class SCTenantController extends Controller
{
    public function index(HaloService $haloService)
    {
        $sctenants = SCTenant::orderBy('name', 'desc')
            ->paginate(50);
        $tenantsCount = [
            'all' => SCTenant::count(),
            'usage' => SCTenant::where('billingType', 'usage')->count(),
            'trail' => SCTenant::where('billingType', 'trail')->count(),
            'term' => SCTenant::where('billingType', 'term')->count(),
        ];
        return view('sctenants.index', compact('sctenants', 'tenantsCount'));
    }

    public function show(string $id)
    {
        $sctenant = SCTenant::findOrFail($id);
        $scalerts = $sctenant->SCAlerts()->orderByDesc('raisedAt')->paginate(50);
        $scbillables = $sctenant->SCBillables()
            ->where('year', '>=', now()->format('Y'))
            ->where('month', '>=', now()->format('m'))
            ->get();
        return view('sctenants.show', 
            compact('sctenant',
                'scalerts', 'scbillables'));
    }
}
