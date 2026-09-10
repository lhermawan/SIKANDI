<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\ConfigurationItem;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AgentController extends Controller
{
    public function index()
    {
        $agents = Agent::with('configurationItem')->latest()->paginate(15);
        $registrationToken = SystemSetting::where('key', 'agent_registration_token')->value('value');
        
        return view('agents.index', compact('agents', 'registrationToken'));
    }

    public function show(Agent $agent)
    {
        $agent->load(['configurationItem', 'services', 'events' => function($q) {
            $q->latest()->limit(50);
        }]);
        $latestMetric = $agent->metrics()->latest()->first();
        $cis = ConfigurationItem::whereHas('ciType', function($q) {
            $q->where('name', 'like', '%Server%')->orWhere('code', 'SRV'); // fallback
        })->get();

        return view('agents.show', compact('agent', 'latestMetric', 'cis'));
    }

    public function approve(Agent $agent)
    {
        $agent->update([
            'status' => 'online',
            'approved_at' => now(),
            'approved_by' => auth()->id()
        ]);

        // Generate token
        $agent->tokens()->delete(); // Remove old tokens if any
        $token = $agent->createToken('agent-token')->plainTextToken;

        return back()->with('success', 'Agent approved. Please copy this token to configure the agent: ' . $token);
    }

    public function revoke(Agent $agent)
    {
        $agent->tokens()->delete();
        $agent->update(['status' => 'revoked']);
        return back()->with('success', 'Agent access has been revoked.');
    }

    public function link(Request $request, Agent $agent)
    {
        $request->validate(['ci_id' => 'nullable|exists:configuration_items,id']);
        $agent->update(['ci_id' => $request->ci_id]);
        return back()->with('success', 'Agent CI link updated.');
    }

    public function generateRegistrationToken()
    {
        $token = Str::random(32);
        SystemSetting::updateOrCreate(
            ['key' => 'agent_registration_token'],
            ['value' => $token, 'type' => 'string', 'description' => 'Token used by new agents to register']
        );
        return back()->with('success', 'New Registration Token generated.');
    }
}
