<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventsRequest;
use App\Models\Event;

class EventController extends Controller
{
    public function index(EventsRequest $request)
    {
        $validated = $request->validated();


        $events = Event::orderBy('id', 'desc');
        
        $hideInfo = $validated['hideInfo'] ?? false;
        if($hideInfo) {
            $events = $events->where('type', '!=', 'info');
        }

        $events = $events->paginate(50);
        return view('events.index', compact('events', 'hideInfo'));
    }
}
