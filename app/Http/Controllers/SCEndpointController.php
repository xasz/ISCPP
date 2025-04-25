<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SCEndpoint;

class SCEndpointController extends Controller
{
    public function index()
    {

        $validated = collect(request()->validate([
            'filterHostname' => 'nullable|string|max:255',
        ]));

        $scendpoints = SCEndpoint::orderBy('hostname', 'desc')
            ->when($validated->has('filterHostname'), function ($query) use ($validated) {
                $query->where('hostname', 'ILIKE', '%' . $validated['filterHostname'] . '%');
            })
            ->paginate(50);

        $endpointsCount = [
            'all' => SCEndpoint::count(),
        ];
        return view('scendpoints.index', compact('scendpoints', 'endpointsCount'));
    }
}
