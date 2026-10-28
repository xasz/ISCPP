<?php

namespace App\Http\Controllers;

use App\Models\SCEndpoint;

class SCEndpointController extends Controller
{
    public function index()
    {
        $validated = collect(request()->validate([
            'filterHostname' => 'nullable|string|max:255',
            'filterHealth' => 'nullable|string|in:good,suspicious,bad,unknown',
            'filterType' => 'nullable|string|in:computer,server',
        ]))->filter();

        $scendpoints = SCEndpoint::with('SCTenant')
            ->orderBy('hostname')
            ->when($validated->has('filterHostname'), function ($query) use ($validated) {
                $query->whereLike('hostname', '%'.$validated['filterHostname'].'%');
            })
            ->when($validated->has('filterHealth'), function ($query) use ($validated) {
                $query->where('healthStatus', $validated['filterHealth']);
            })
            ->when($validated->has('filterType'), function ($query) use ($validated) {
                $query->where('type', $validated['filterType']);
            })
            ->paginate(50);

        $endpointsCount = [
            'all' => SCEndpoint::count(),
            'good' => SCEndpoint::where('healthStatus', 'good')->count(),
            'suspicious' => SCEndpoint::where('healthStatus', 'suspicious')->count(),
            'bad' => SCEndpoint::where('healthStatus', 'bad')->count(),
        ];

        return view('scendpoints.index', compact('scendpoints', 'endpointsCount'));
    }

    public function endpointDetails(string $id)
    {
        $endpoint = SCEndpoint::with('SCTenant')->findOrFail($id);

        return view('scendpoints.show.details', compact('endpoint'));
    }

    public function endpointLive(string $id)
    {
        $endpoint = SCEndpoint::with('SCTenant')->findOrFail($id);

        return view('scendpoints.show.live', compact('endpoint'));
    }

    public function endpointRaw(string $id)
    {
        $endpoint = SCEndpoint::with('SCTenant')->findOrFail($id);

        return view('scendpoints.show.raw', compact('endpoint'));
    }
}
