<?php

namespace App\Http\Middleware;

use App\Settings\HaloServiceSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsHaloIntegrationEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!resolve(HaloServiceSettings::class)->enabled){
            return response()->json([
                'message' => 'Halo Service is not enabled.',
            ], 403);
        }
        return $next($request);
    }
}
