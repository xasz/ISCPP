<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SCEndpoint;

class SCEndpointController extends Controller
{
    public function index()
    {
        $scendpoints = SCEndpoint::orderBy('name', 'desc')
            ->paginate(50);
        $endpointsCount = [
            'all' => SCEndpoint::count(),
        ];
        return view('scendpoints.index', compact('scendpoints', 'endpointsCount'));
    }
}
