<?php

namespace App\Http\Controllers;

use App\Jobs\PushToHaloSCTenantSCBillables;
use App\Models\SCBillable ;
use App\Http\Requests\SCBillableRequest;
use App\Models\SCTenant;
use App\Services\HaloService;

class SCBillableController extends Controller
{
    public function index(SCBillableRequest $request)
    {

        $validated = $request->validated();
        
        $year = $validated['year'] ?? date('Y');
        $month = $validated['month'] ?? date('m');

        $scbillables = SCBillable::where('year', $year)
            ->where('month', $month)
            ->orderBy('id', 'desc')
            ->paginate(200);

        return view('scbillables.index', compact('year', 'month', 'scbillables'));
    }

    public function show(string $id)
    {
        $scbillable = SCBillable::with('SCTenant')->findOrFail($id);
        
        return view('scbillables.show', compact('scbillable'));
    }

    public function dispatchToHaloAndShow(string $id)
    {
        $scbillable = SCBillable::findOrFail($id);
        /*if($scbillable->sent_to_halo == 'pending'){
            abort(403, 'Already planned to be sent to Halo');
        }
        $scbillable->update(['sent_to_halo' => 'pending']);*/
        $scbillable->dispatchToHalo();        
        return redirect()->route('scbillables.show', $id);
    }

    public function dispatchToHaloAndShowTenant(int $year, int $month, string $id){
        $sctenant = SCTenant::findOrFail($id);
        PushToHaloSCTenantSCBillables::dispatch($sctenant, $year, $month);
        return redirect()->route('sctenants.show', $sctenant );
    }

    public function haloPusher(){
        return view('scbillables.haloPusher');        
    }
}
