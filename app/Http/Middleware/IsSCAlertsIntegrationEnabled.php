<?php

namespace App\Http\Middleware;

use App\Settings\SCServiceSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSCAlertsIntegrationEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!resolve(SCServiceSettings::class)->alertsScheduleEnabled){
            return response()->json([
                'message' => 'Alerts Module is not enabled.',
            ], 403);
        }
        return $next($request);
    }
}
