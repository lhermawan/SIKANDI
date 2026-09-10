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
        $incident->load(['organization', 'configurationItem', 'reporter', 'assignedLead', 'events', 'agent', 'tasks', 'evidence', 'responses', 'socAuditLogs.user', 'assignments.user']);
        
        // Auto-seed default tasks if none exist
        if ($incident->tasks()->count() === 0) {
            $defaultTasks = [
                ['category' => 'Authentication', 'task' => 'Failed login confirmed'],
                ['category' => 'Authentication', 'task' => 'Successful login checked'],
                ['category' => 'Authentication', 'task' => 'Authentication logs reviewed'],
                ['category' => 'Account', 'task' => 'Session checked'],
                ['category' => 'Persistence', 'task' => 'Suspicious processes checked'],
            ];
            foreach ($defaultTasks as $dt) {
                $incident->tasks()->create(array_merge($dt, ['status' => 'PENDING']));
            }
            $incident->load('tasks');
        }

        return view('security.incidents-show', compact('incident'));
    }

    private function logAudit(SecurityIncident $incident, $action, $oldValue = null, $newValue = null)
    {
        \App\Models\SecurityIncidentAuditLog::create([
            'incident_id' => $incident->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function updateWorkflow(Request $request, SecurityIncident $incident): RedirectResponse
    {
        $validated = $request->validate([
            'workflow_status' => 'required|in:reported,investigating,contained,compromised,recovery,resolved,false_positive,duplicate',
        ]);

        $oldStatus = $incident->workflow_status;
        $newStatus = $validated['workflow_status'];

        if ($oldStatus !== $newStatus) {
            $incident->update(['workflow_status' => $newStatus]);
            
            if ($newStatus === 'investigating' && empty($incident->assigned_lead_id)) {
                $incident->update(['assigned_lead_id' => Auth::id()]);
            }
            
            if ($newStatus === 'contained' && empty($incident->contained_at)) {
                $incident->update(['contained_at' => now()]);
            }

            $this->logAudit($incident, 'changed status', strtoupper($oldStatus), strtoupper($newStatus));
        }

        return back()->with('success', 'Status insiden berhasil diperbarui.');
    }

    public function assign(Request $request, SecurityIncident $incident)
    {
        $validated = $request->validate(['user_id' => 'required|exists:users,id']);
        
        $oldLead = $incident->assignedLead ? $incident->assignedLead->name : 'Unassigned';
        $incident->update(['assigned_lead_id' => $validated['user_id']]);
        
        \App\Models\SecurityIncidentAssignment::create([
            'incident_id' => $incident->id,
            'user_id' => $validated['user_id'],
            'assigned_by' => Auth::id(),
            'assigned_at' => now(),
        ]);

        $newLead = \App\Models\User::find($validated['user_id'])->name;
        $this->logAudit($incident, 'assigned incident', $oldLead, $newLead);

        return back()->with('success', 'Insiden berhasil di-assign.');
    }

    public function storeTask(Request $request, SecurityIncident $incident)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'task' => 'required|string',
        ]);

        $incident->tasks()->create(array_merge($validated, ['status' => 'PENDING']));
        $this->logAudit($incident, 'added task', null, $validated['task']);

        return back();
    }

    public function toggleTask(Request $request, SecurityIncident $incident, \App\Models\SecurityIncidentTask $task)
    {
        $newStatus = $task->status === 'PENDING' ? 'COMPLETED' : 'PENDING';
        $task->update([
            'status' => $newStatus,
            'checked_by' => $newStatus === 'COMPLETED' ? Auth::id() : null,
            'checked_at' => $newStatus === 'COMPLETED' ? now() : null,
        ]);
        
        return back();
    }

    public function storeResponse(Request $request, SecurityIncident $incident)
    {
        $validated = $request->validate([
            'action' => 'required|string',
            'description' => 'required|string',
        ]);

        $incident->responses()->create([
            'action' => $validated['action'],
            'description' => $validated['description'],
            'status' => 'COMPLETED',
            'performed_by' => Auth::id(),
            'performed_at' => now(),
        ]);

        $this->logAudit($incident, 'performed response', null, $validated['action']);
        return back()->with('success', 'Tindakan respons berhasil dicatat.');
    }

    public function storeEvidence(Request $request, SecurityIncident $incident)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'title' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $incident->evidence()->create(array_merge($validated, [
            'collected_by' => Auth::id(),
            'collected_at' => now(),
        ]));

        $this->logAudit($incident, 'added evidence', null, $validated['title']);
        return back()->with('success', 'Bukti berhasil ditambahkan.');
    }

    public function resolve(Request $request, SecurityIncident $incident)
    {
        $validated = $request->validate([
            'resolution_type' => 'required|string',
            'root_cause' => 'required|string',
            'resolution_summary' => 'required|string',
        ]);

        $oldStatus = $incident->workflow_status;

        $incident->update([
            'resolution_type' => $validated['resolution_type'],
            'root_cause' => $validated['root_cause'],
            'resolution_summary' => $validated['resolution_summary'],
            'workflow_status' => 'resolved',
            'resolved_at' => now(),
            'closed_at' => now(),
        ]);

        $this->logAudit($incident, 'resolved incident', strtoupper($oldStatus), 'RESOLVED');

        return back()->with('success', 'Insiden berhasil diselesaikan (Resolved).');
    }

    public function destroy(SecurityIncident $incident)
    {
        $incident->delete();
        return redirect()->route('security.incidents.index')->with('success', 'Insiden Keamanan Siber berhasil dihapus.');
    }
}
