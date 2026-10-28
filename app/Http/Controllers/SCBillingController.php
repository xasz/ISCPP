<?php

namespace App\Http\Controllers;

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
}
