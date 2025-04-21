<?php

namespace App\Http\Controllers;

use App\Models\SCTenant;
use App\Models\SCBillable;
use App\Http\Requests\SCBillableRequest;
use Illuminate\Http\Request;

class SCBillingController extends Controller
{

    public function fetcher()
    {
        return view('scbilling.fetcher');
    }

    public function haloPusher()
    {
        return view('scbilling.haloPusher');
    }
    public function haloSettings()
    {

        return view('scbilling.haloSettings',
            [
                'SCTenants' => SCTenant::where('haloclient_id', '<=', 0)->get(),
            ]
        );
    }
}
