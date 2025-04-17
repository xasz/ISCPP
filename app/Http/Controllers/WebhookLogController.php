<?php

namespace App\Http\Controllers;

use App\Models\WebhookLog;

class WebhookLogController extends Controller
{
    public function index()
    {
        $webhookLogs = WebhookLog::orderBy('created_at', 'desc')
            ->paginate(50);
        return view('webhookLog.index', compact('webhookLogs'));
    }
}
