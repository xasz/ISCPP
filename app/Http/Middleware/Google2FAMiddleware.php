<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Google2FAMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Skip if user is not logged in or 2FA is not enabled
        if (!$user || !$user->google2fa_enabled) {
            return $next($request);
        }

        // Skip if 2FA has already been verified in the current session
        if ($request->session()->get('google2fa_verified')) {
            return $next($request);
        }

        // Store the intended URL for redirecting after 2FA verification
        $request->session()->put('url.intended', $request->url());

        // Redirect to the 2FA verification page
        return redirect()->route('2fa.verify');
    }
}
