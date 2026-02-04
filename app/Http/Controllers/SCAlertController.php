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
    public function index()
    {
        $scalerts = SCAlert::orderBy('raisedAt', 'desc')
            ->with('SCTenant')
            ->paginate(50);


        $chartData = collect([]);
        if(env('DB_CONNECTION') != 'sqlite'){            
            /*$chartData = SCAlert::selectRaw("date_format(sc_alerts.raisedAt, '%Y-%m-%d') as date, count(*) as aggregate")
            ->whereDate('raisedAt', '>=', now()->subDays(30))
            ->groupBy('date')
            ->get();*/
            $chartData = SCAlert::whereDate('raisedAt', '>=', now()->subDays(30))->get();
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
            'all' => SCAlert::whereDate('raisedAt', '>=', now()->subHours(24))
                ->count(),
            'low' => SCAlert::whereDate('raisedAt', '>=', now()->subHours(24))
                ->where('severity', 'low')->count(),
            'medium' => SCAlert::whereDate('raisedAt', '>=', now()->subHours(24))
                ->where('severity', 'medium')->count(),
            'high' => SCAlert::whereDate('raisedAt', '>=', now()->subHours(24))
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
