<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SCFirewall;

class SCFirewallController extends Controller
{
    public function index()
    {
        $scfirewalls = SCFirewall::orderBy('hostname', 'desc')
            ->paginate(50);
        $firewallsCount = [
            'all' => SCFirewall::count(),
        ];
        return view('scfirewalls.index', compact('scfirewalls', 'firewallsCount'));
    }
}
