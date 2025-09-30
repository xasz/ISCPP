<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\SCAlert;
use App\Models\SCTenant;
use App\Services\HaloService;
use App\Settings\HaloServiceSettings;
use App\Settings\NinjaServiceSettings;
use App\Settings\SCServiceSettings;
use App\Settings\WebhookSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class DashboardController extends Controller
{
    public function index(  SCServiceSettings $sCServiceSettings, 
                            HaloServiceSettings $haloServiceSettings,
                            NinjaServiceSettings $ninjaServiceSettings,
    )   
    {
        $awareness = collect();
        if(config('app.env') != 'production') {
            $awareness->push([
                'message' => 'This instance of ISCPP is not running as production environment.',
            ]);
        }

        if(Event::where('type', 'error')
            ->where('created_at', '>=', Carbon::now()->subDays(2))
            ->orderBy('created_at', 'desc')
            ->count() > 0){
                $awareness->push([
                'message' => 'There are errors in the event log from the last 48 hours. Please check the system event log for details.',
            ]);
        }
        
        if($sCServiceSettings->clientId == null || $sCServiceSettings->clientId == '') {
            $awareness->push([
                'message' => 'Sophos Central Service Client ID is not set. Please configure the Sophos Central Service settings.',
            ]);
        }

        if($haloServiceSettings->enabled) {
            if($haloServiceSettings->clientId == null || $haloServiceSettings->clientId == '') {
                $awareness->push([
                    'message' => 'Halo Service is enabled, but Client ID is not set. Please configure the Halo Service settings.',
                ]);
            }

            if(SCTenant::where('haloclient_id', '<=', 0)->count() > 0) {
                $awareness->push([
                    'message' => 'Halo Service is enabled, but some tenants do not have a Halo Client ID. Please configure the Halo Client ID for all tenants.',
                ]);
            }
        }
        if($ninjaServiceSettings->enabled) {
            if($ninjaServiceSettings->clientId == null || $ninjaServiceSettings->clientId == '') {
                $awareness->push([
                    'message' => 'NinjaOne Service is enabled, but Client ID is not set. Please configure the NinjaOne Service settings.',
                ]);
            }
            if(SCTenant::where('ninjaorg_id', '<=', 0)->count() > 0) {
                $awareness->push([
                    'message' => 'NinjaOne Service is enabled, but some tenants do not have a NinjaOne Client ID. Please configure the NinjaOne Client ID for all tenants.',
                ]);
            }
        }

        return view('dashboard', [
            'awareness' => $awareness,
            'tenantsCount' => SCTenant::count(),
            'alerts24HCount' => SCAlert::whereDate('raisedAt', '>=', now()->subHours(24))->count(),
            'jobsInQueue' => DB::connection(config('queue.connections.' . config('queue.default') . '.connection'))->table('jobs')->count(),
        ]);
    }

}
