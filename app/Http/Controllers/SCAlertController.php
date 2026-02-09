<?php

namespace App\Http\Controllers;

use App\Models\SCAlert;
use App\Settings\WebhookSettings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LaravelDaily\LaravelCharts\Classes\LaravelChart;

class SCAlertController extends Controller
{
    public function index(Request $request)
    {
        $query = SCAlert::orderBy('raisedAt', 'desc');
        if ($request->filled('hide_acknowledged')) {
            $query->where('is_acknowledged', false);
        }
        
        if($request->filled('severity_high') || $request->filled('severity_medium') || $request->filled('severity_low')){
            $query->where(function($q) use ($request){
                if ($request->filled('severity_high')) {
                    $q->orWhere('severity', 'high');
                }
    
                if ($request->filled('severity_medium')) {
                    $q->orWhere('severity', 'medium');
                }
    
                if ($request->filled('severity_low')) {
                    $q->orWhere('severity', 'low');
                }
            });
        }

        $scalerts = $query->clone()->with('SCTenant')->paginate(50)->appends($request->all());


        $chartData = collect([]);
        if(env('DB_CONNECTION') != 'sqlite'){            
            /*$chartData = SCAlert::selectRaw("date_format(sc_alerts.raisedAt, '%Y-%m-%d') as date, count(*) as aggregate")
            ->whereDate('raisedAt', '>=', now()->subDays(30))
            ->groupBy('date')
            ->get();*/
            $chartData = $query->clone()->whereDate('raisedAt', '>=', now()->subDays(30))->get();
            $chartData = $chartData->groupBy(function ($item, int $key) {
                return $item->raisedAt->format('Y-m-d');
            })->map(function ($item, $key) {
                return [
                    'date' => $key,
                    'aggregate' => $item->count()
                ];
            })->sortBy(function ($item, $key) {
                $c = Carbon::createFromFormat('Y-m-d', $item['date']);
                return $c->timestamp;
            });
            $chartData = $chartData->values();
        }

        $alertsCount = [
            'all' => $query->clone()->whereDate('raisedAt', '>=', now()->subHours(24))
                ->count(),
            'low' => $query->clone()->whereDate('raisedAt', '>=', now()->subHours(24))
                ->where('severity', 'low')->count(),
            'medium' => $query->clone()->whereDate('raisedAt', '>=', now()->subHours(24))
                ->where('severity', 'medium')->count(),
            'high' => $query->whereDate('raisedAt', '>=', now()->subHours(24))
                ->where('severity', 'high')->count(),
        ];

        return view('scalerts.index', compact('scalerts', 'chartData', 'alertsCount'));
    }

    public function show(string $id)
    {
        $scalert = SCAlert::with('SCTenant')->findOrFail($id);
        $webhooksEnabled = app(WebhookSettings::class)->enabled;
        
        return view('scalerts.show', compact('scalert', 'webhooksEnabled'));
    }

    public function autoActions()
    {
        return view('scalerts.autoActions');
    }

    public function dispatchAndShow(string $id)
    {
        $scalert = SCAlert::with('SCTenant')->findOrFail($id);
        $scalert->dispatchWebhook();
        return $this->show($id);
    }


}
