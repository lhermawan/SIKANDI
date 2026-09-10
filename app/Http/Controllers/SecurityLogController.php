<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\SecurityEvent;
use Illuminate\Http\Request;

class SecurityLogController extends Controller
{
    public function index(Request $request)
    {
        $agentId = $request->input('agent_id');
        $search = $request->input('search');

        $query = SecurityEvent::with(['agent', 'incident'])
            ->when($agentId, function ($q) use ($agentId) {
                $q->where('agent_id', $agentId);
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('event_type', 'like', '%' . $search . '%')
                        ->orWhere('hostname', 'like', '%' . $search . '%')
                        ->orWhere('username', 'like', '%' . $search . '%')
                        ->orWhere('source_ip', 'like', '%' . $search . '%')
                        ->orWhere('action', 'like', '%' . $search . '%');
                });
            })
            ->latest('timestamp');

        $events = $query->paginate(50)->withQueryString();
        $agents = Agent::orderBy('hostname')->get(['id', 'hostname', 'agent_id']);
        $selectedAgent = $agentId ? Agent::find($agentId) : null;

        return view('security.logs.index', compact('events', 'agents', 'selectedAgent', 'agentId', 'search'));
    }

    public function show(SecurityEvent $event)
    {
        $event->load(['agent', 'incident']);
        return view('security.logs.show', compact('event'));
    }
}
