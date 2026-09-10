<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\ConfigurationItem;
use App\Models\Incident;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class IncidentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Incident::with(['configurationItem.ciType', 'asset', 'organization', 'assignedTechnician', 'ticket']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        $incidents = $query->latest()->paginate(15)->withQueryString();

        return view('incidents.index', compact('incidents'));
    }

    public function create(): View
    {
        $cis = ConfigurationItem::orderBy('name')->get();
        $assets = Asset::orderBy('name')->get();
        $organizations = Organization::orderBy('name')->get();

        return view('incidents.create', compact('cis', 'assets', 'organizations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'source' => 'required|in:service_desk,monitoring,security_monitoring,manual_report',
            'ci_id' => 'nullable|exists:configuration_items,id',
            'asset_id' => 'nullable|exists:assets,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'priority' => 'required|in:critical,high,medium,low',
            'status' => 'required|in:open,investigation,in_progress,resolved,closed',
            'impact_description' => 'nullable|string',
            'root_cause' => 'nullable|string',
        ]);

        $validated['detected_at'] = now();
        $incident = Incident::create($validated);

        return redirect()->route('incidents.show', $incident)->with('success', "Insiden {$incident->incident_number} berhasil didaftarkan.");
    }

    public function show(Incident $incident): View
    {
        $incident->load([
            'configurationItem.ciType',
            'asset',
            'organization',
            'assignedTechnician',
            'ticket',
            'comments.user',
        ]);

        $technicians = User::role(['IT Technician', 'Admin Persandian', 'Super Admin'])->get();

        return view('incidents.show', compact('incident', 'technicians'));
    }

    public function update(Request $request, Incident $incident): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:open,investigation,in_progress,resolved,closed',
            'priority' => 'required|in:critical,high,medium,low',
            'assigned_technician_id' => 'nullable|exists:users,id',
            'root_cause' => 'nullable|string',
            'resolution' => 'nullable|string',
        ]);

        if (in_array($validated['status'], ['resolved', 'closed']) && empty($incident->resolved_at)) {
            $validated['resolved_at'] = now();
        }

        $incident->update($validated);

        return back()->with('success', 'Perkembangan insiden berhasil diperbarui.');
    }

    public function addComment(Request $request, Incident $incident): RedirectResponse
    {
        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        $incident->comments()->create([
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
        ]);

        return back()->with('success', 'Catatan investigasi insiden berhasil ditambahkan.');
    }

    public function destroy(Incident $incident)
    {
        $incident->delete();
        return back()->with('success', 'Insiden Service Desk berhasil dihapus.');
    }
}
