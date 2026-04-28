<?php

namespace App\Http\Controllers;

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

    public function firewallDetails(string $id)
    {
        $firewall = SCFirewall::with('SCTenant')->findOrFail($id);

        return view('scfirewalls.show.details', compact('firewall'));
    }

    public function firewallRaw(string $id)
    {
        $firewall = SCFirewall::with('SCTenant')->findOrFail($id);

        return view('scfirewalls.show.raw', compact('firewall'));
    }
}
