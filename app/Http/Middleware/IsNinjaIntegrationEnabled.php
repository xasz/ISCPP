<?php

namespace App\Http\Middleware;

use App\Settings\NinjaServiceSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsNinjaIntegrationEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!resolve(NinjaServiceSettings::class)->enabled){
            return response()->json([
                'message' => 'Ninja Service is not enabled.',
            ], 403);
        }
        return $next($request);
    }
}
