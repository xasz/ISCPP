<?php

namespace App\Http\Controllers;

use App\Models\SCAlert;
use App\Models\SCTenant;
use App\Settings\WebhookSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
      
        return view('dashboard', [
            'tenantsCount' => SCTenant::count(),
            'alerts24HCount' => SCAlert::whereDate('raisedAt', '>=', now()->subHours(24))->count(),
            'jobsInQueue' => DB::table('jobs')->count(),
        ]);
    }

}
