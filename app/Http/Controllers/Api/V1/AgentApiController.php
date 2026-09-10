<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentEvent;
use App\Models\AgentMetric;
use App\Models\AgentService;
use App\Models\Incident;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AgentApiController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'registration_token' => 'required|string',
            'hostname' => 'required|string',
            'os' => 'nullable|string',
            'os_version' => 'nullable|string',
            'agent_version' => 'nullable|string',
            'ip_address' => 'nullable|string',
        ]);

        $validToken = SystemSetting::where('key', 'agent_registration_token')->value('value');
        
        // If not set in DB, allow testing with a default or throw error
        if (!$validToken || $request->registration_token !== $validToken) {
            return response()->json(['message' => 'Invalid registration token'], 401);
        }

        $agentId = 'AGT-' . strtoupper(Str::random(8));

        $agent = Agent::create([
            'agent_id' => $agentId,
            'hostname' => $request->hostname,
            'os' => $request->os,
            'os_version' => $request->os_version,
            'agent_version' => $request->agent_version,
            'ip_address' => $request->ip_address ?? $request->ip(),
            'status' => 'pending',
            'registered_at' => now(),
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'message' => 'Agent registered successfully. Waiting for administrator approval.',
            'agent_id' => $agent->agent_id,
            'status' => 'pending'
        ], 201);
    }

    public function heartbeat(Request $request)
    {
        $agent = $request->user();
        if (!$agent instanceof Agent) {
            return response()->json(['message' => 'Unauthorized. Token does not belong to an agent.'], 403);
        }

        if ($agent->status === 'revoked' || $agent->status === 'disabled') {
            return response()->json(['message' => 'Agent is ' . $agent->status], 403);
        }

        $agent->update([
            'last_seen_at' => now(),
            'status' => 'online',
        ]);

        return response()->json(['message' => 'Heartbeat acknowledged']);
    }

    public function metrics(Request $request)
    {
        $agent = $request->user();
        if (!$agent instanceof Agent) return response()->json(['message' => 'Unauthorized'], 403);

        $request->validate([
            'cpu_usage' => 'nullable|numeric',
            'memory_total' => 'nullable|numeric',
            'memory_used' => 'nullable|numeric',
            'memory_usage' => 'nullable|numeric',
            'disk_total' => 'nullable|numeric',
            'disk_used' => 'nullable|numeric',
            'disk_usage' => 'nullable|numeric',
            'uptime_seconds' => 'nullable|numeric',
        ]);

        AgentMetric::create(array_merge($request->only([
            'cpu_usage', 'memory_total', 'memory_used', 'memory_usage',
            'disk_total', 'disk_used', 'disk_usage', 'uptime_seconds'
        ]), ['agent_id' => $agent->id]));

        return response()->json(['message' => 'Metrics recorded']);
    }

    public function services(Request $request)
    {
        $agent = $request->user();
        if (!$agent instanceof Agent) return response()->json(['message' => 'Unauthorized'], 403);

        $request->validate([
            'services' => 'required|array',
            'services.*.name' => 'required|string',
            'services.*.status' => 'required|string',
        ]);

        foreach ($request->services as $svc) {
            AgentService::updateOrCreate(
                ['agent_id' => $agent->id, 'service_name' => $svc['name']],
                ['status' => $svc['status'], 'last_checked_at' => now()]
            );
        }

        return response()->json(['message' => 'Services updated']);
    }

    public function events(Request $request)
    {
        $agent = $request->user();
        if (!$agent instanceof Agent) return response()->json(['message' => 'Unauthorized'], 403);

        $request->validate([
            'type' => 'required|string',
            'severity' => 'required|string',
            'message' => 'nullable|string',
            'payload' => 'nullable|array',
        ]);

        if ($request->type === 'security_event' && isset($request->payload)) {
            // Process via Detection Engine
            $engine = new \App\Services\SecurityDetectionEngine();
            $engine->processEvent($agent, $request->payload);
            return response()->json(['message' => 'Security Event recorded']);
        }

        // Generic Agent Event
        $event = AgentEvent::create([
            'agent_id' => $agent->id,
            'type' => $request->type,
            'severity' => $request->severity,
            'message' => $request->message,
            'payload' => $request->payload,
        ]);

        // Integrate with Incidents if service goes down
        if ($request->type === 'service_down' && $agent->ci_id) {
            $serviceName = $request->payload['service'] ?? 'Unknown Service';
            
            // Check if there's already an active incident for this
            $existingIncident = Incident::where('ci_id', $agent->ci_id)
                ->where('status', '!=', 'resolved')
                ->where('title', 'like', "%$serviceName is down%")
                ->first();

            if (!$existingIncident) {
                $incident = Incident::create([
                    'title' => "Monitoring Alert: $serviceName is down on " . $agent->hostname,
                    'source' => 'monitoring',
                    'impact_description' => "Automated alert from Agent " . $agent->agent_id . ".\n\nMessage: " . $request->message,
                    'status' => 'open',
                    'priority' => 'high',
                    'detected_at' => now(),
                    'ci_id' => $agent->ci_id,
                ]);
                $event->update(['incident_id' => $incident->id]);
            } else {
                $event->update(['incident_id' => $existingIncident->id]);
            }
        } elseif ($request->type === 'service_recovery' && $agent->ci_id) {
            $serviceName = $request->payload['service'] ?? 'Unknown Service';
            $existingIncident = Incident::where('ci_id', $agent->ci_id)
                ->where('status', '!=', 'resolved')
                ->where('title', 'like', "%$serviceName is down%")
                ->first();

            if ($existingIncident) {
                $existingIncident->update([
                    'status' => 'resolved',
                    'resolution' => 'Auto-resolved by Agent: Service recovered.',
                    'resolved_at' => now(),
                ]);
                $event->update(['incident_id' => $existingIncident->id]);
            }
        }

        return response()->json(['message' => 'Event recorded']);
    }
}
