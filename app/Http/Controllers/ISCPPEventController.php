<?php

namespace App\Http\Controllers;

use App\Models\ISCPPEventLog;
use App\Models\ISCPPEventRule;
use App\Models\SCTenant;
use App\Services\ISCPPEventRegistry;
use Illuminate\Http\Request;

class ISCPPEventController extends Controller
{
    public function index(Request $request, ISCPPEventRegistry $registry)
    {
        $validated = $request->validate([
            'key' => 'nullable|string|max:255',
            'severity' => 'nullable|string|in:info,warning,error,critical',
            'tenantId' => 'nullable|string|max:64',
        ]);

        $events = ISCPPEventLog::query()
            ->ofKey($validated['key'] ?? null)
            ->ofSeverity($validated['severity'] ?? null)
            ->ofTenant($validated['tenantId'] ?? null)
            ->with('SCTenant')
            ->orderByDesc('id')
            ->paginate(50);

        return view('iscpp.events.index', [
            'events' => $events,
            'eventOptions' => $registry->options(),
            'filters' => $validated,
            'sctenants' => SCTenant::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(ISCPPEventLog $iscppEvent)
    {
        $iscppEvent->load(['SCTenant', 'deliveries.rule']);

        return view('iscpp.events.show', ['event' => $iscppEvent]);
    }

    public function info()
    {
        return view('iscpp.info.index');
    }

    public function rules()
    {
        return view('iscpp.rules.index');
    }

    public function createRule()
    {
        return view('iscpp.rules.form', ['ruleId' => null]);
    }

    public function editRule(ISCPPEventRule $iscppEventRule)
    {
        return view('iscpp.rules.form', ['ruleId' => $iscppEventRule->id]);
    }
}
