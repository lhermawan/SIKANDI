<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\ConfigurationItem;
use App\Models\Organization;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = Ticket::with(['requester', 'organization', 'service', 'assignedTechnician', 'configurationItem']);

        // If OPD User, only view own organization's tickets
        if ($user->hasRole('OPD User')) {
            $query->where('organization_id', $user->organization_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('ticket_number', 'like', "%{$s}%")
                    ->orWhere('title', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%");
            });
        }

        $tickets = $query->latest()->paginate(15)->withQueryString();
        $services = Service::where('is_active', true)->get();

        return view('service-desk.index', compact('tickets', 'services'));
    }

    public function create(): View
    {
        $user = Auth::user();
        $services = Service::where('is_active', true)->get();
        $cis = ConfigurationItem::where('status', 'active');
        $assets = Asset::where('lifecycle_status', 'in_use');

        if ($user->hasRole('OPD User') && $user->organization_id) {
            $cis->where('organization_id', $user->organization_id);
            $assets->where('organization_id', $user->organization_id);
        }

        $cis = $cis->orderBy('name')->get();
        $assets = $assets->orderBy('name')->get();

        return view('service-desk.create', compact('services', 'cis', 'assets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'service_id' => 'nullable|exists:services,id',
            'category' => 'required|in:service_request,incident,question,access_request,maintenance',
            'priority' => 'required|in:critical,high,medium,low',
            'ci_id' => 'nullable|exists:configuration_items,id',
            'asset_id' => 'nullable|exists:assets,id',
            'description' => 'required|string',
        ]);

        $validated['requester_id'] = $user->id;
        $validated['organization_id'] = $user->organization_id ?? 1;
        $validated['status'] = 'open';

        if (! empty($validated['service_id'])) {
            $service = Service::find($validated['service_id']);
            if ($service && $service->sla_resolution_hours) {
                $validated['sla_due_at'] = now()->addHours($service->sla_resolution_hours);
            }
        }

        $ticket = Ticket::create($validated);

        return redirect()->route('service-desk.tickets.show', $ticket)->with('success', "Tiket {$ticket->ticket_number} berhasil diajukan.");
    }

    public function show(Ticket $ticket): View
    {
        $user = Auth::user();
        if ($user->hasRole('OPD User') && $ticket->organization_id !== $user->organization_id) {
            abort(403, 'Anda tidak memiliki akses ke tiket ini.');
        }

        $ticket->load([
            'requester',
            'organization',
            'service',
            'configurationItem.ciType',
            'asset',
            'assignedTechnician',
            'comments.user',
            'incidents',
        ]);

        $technicians = User::role(['IT Technician', 'Admin Persandian', 'Super Admin'])->get();

        return view('service-desk.show', compact('ticket', 'technicians'));
    }

    public function addComment(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'comment' => 'required|string',
            'is_internal' => 'nullable|boolean',
        ]);

        $ticket->comments()->create([
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
            'is_internal' => $request->boolean('is_internal'),
        ]);

        return back()->with('success', 'Catatan berhasil ditambahkan ke percakapan tiket.');
    }

    public function updateStatus(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:open,assigned,in_progress,waiting,resolved,closed',
            'assigned_technician_id' => 'nullable|exists:users,id',
            'resolution_notes' => 'nullable|string',
        ]);

        if ($validated['status'] === 'resolved' && empty($ticket->resolved_at)) {
            $validated['resolved_at'] = now();
        }
        if ($validated['status'] === 'closed' && empty($ticket->closed_at)) {
            $validated['closed_at'] = now();
        }

        $ticket->update($validated);

        return back()->with('success', 'Status tiket berhasil diperbarui.');
    }
}
