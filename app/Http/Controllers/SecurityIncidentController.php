<?php

namespace App\Http\Controllers;

use App\Models\ConfigurationItem;
use App\Models\Organization;
use App\Models\SecurityIncident;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SecurityIncidentController extends Controller
{
    public function index(Request $request): View
    {
        $query = SecurityIncident::with(['organization', 'configurationItem', 'reporter', 'assignedLead']);

        if ($request->filled('type')) {
            $query->where('incident_type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('workflow_status', $request->status);
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        $incidents = $query->latest()->paginate(15)->withQueryString();

        return view('security.incidents', compact('incidents'));
    }

    public function create(): View
    {
        $cis = ConfigurationItem::orderBy('name')->get();
        $organizations = Organization::orderBy('name')->get();

        return view('security.create-incident', compact('cis', 'organizations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'incident_type' => 'required|in:malware,phishing,defacement,unauthorized_access,account_compromise,data_exposure,vulnerability,website_attack,network_attack,other',
            'severity' => 'required|in:critical,high,medium,low',
            'organization_id' => 'required|exists:organizations,id',
            'ci_id' => 'nullable|exists:configuration_items,id',
            'description' => 'required|string',
            'attack_vector' => 'nullable|string',
            'impact_summary' => 'nullable|string',
        ]);

        $validated['reporter_id'] = Auth::id();
        $validated['workflow_status'] = 'reported';

        $incident = SecurityIncident::create($validated);

        return redirect()->route('security.incidents')->with('success', "Insiden Keamanan {$incident->incident_code} berhasil dilaporkan ke CSIRT.");
    }

    public function show(SecurityIncident $incident): View
    {
        $incident->load(['organization', 'configurationItem', 'reporter', 'assignedLead', 'events', 'agent']);
        return view('security.incidents-show', compact('incident'));
    }

    public function updateWorkflow(Request $request, SecurityIncident $incident): RedirectResponse
    {
        $validated = $request->validate([
            'workflow_status' => 'required|in:reported,triage,investigation,containment,eradication,recovery,closed',
            'containment_actions' => 'nullable|string',
            'recovery_actions' => 'nullable|string',
            'assigned_lead_id' => 'nullable|exists:users,id',
        ]);

        if ($validated['workflow_status'] === 'closed' && empty($incident->closed_at)) {
            $validated['closed_at'] = now();
        }

        $incident->update($validated);

        return back()->with('success', 'Alur penanganan insiden siber berhasil diperbarui.');
    }

    public function destroy(SecurityIncident $incident)
    {
        $incident->delete();
        return back()->with('success', 'Insiden Keamanan Siber berhasil dihapus.');
    }
}
