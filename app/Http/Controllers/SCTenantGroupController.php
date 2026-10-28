<?php

namespace App\Http\Controllers;

use App\Models\SCTenantGroup;

class SCTenantGroupController extends Controller
{
    public function index()
    {
        return view('sctenantgroups.index');
    }

    public function create()
    {
        return view('sctenantgroups.form', ['groupId' => null]);
    }

    public function edit(SCTenantGroup $sctenantgroup)
    {
        return view('sctenantgroups.form', ['groupId' => $sctenantgroup->id]);
    }
}
