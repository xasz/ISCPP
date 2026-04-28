<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('generalsettings.sc');
    }

    public function sc(): View
    {
        return view('settings.sc');
    }

    public function webhookAlerts(): View
    {
        return view('settings.webhook-alerts');
    }

    public function halo(): View
    {
        return view('settings.halo');
    }

    public function ninja(): View
    {
        return view('settings.ninja');
    }

    public function commands(): View
    {
        return view('settings.commands');
    }
}
