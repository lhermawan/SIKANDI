<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\CiRelationship;
use App\Models\CiType;
use App\Models\ConfigurationItem;
use App\Models\Location;
use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CmdbController extends Controller
{
    public function index(Request $request): View
    {
        $query = ConfigurationItem::with(['ciType', 'organization', 'asset']);

        if ($request->filled('type')) {
            $query->where('ci_type_id', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('criticality')) {
            $query->where('criticality', $request->criticality);
        }
        if ($request->filled('org')) {
            $query->where('organization_id', $request->org);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('ci_code', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('hostname', 'like', "%{$search}%")
                    ->orWhere('domain', 'like', "%{$search}%");
            });
        }

        $items = $query->latest()->paginate(15)->withQueryString();
        $ciTypes = CiType::where('is_active', true)->get();
        $organizations = Organization::orderBy('name')->get();

        return view('cmdb.index', compact('items', 'ciTypes', 'organizations'));
    }

    public function create(): View
    {
        $ciTypes = CiType::where('is_active', true)->get();
        $organizations = Organization::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $assets = Asset::orderBy('name')->get();

        return view('cmdb.create', compact('ciTypes', 'organizations', 'locations', 'assets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ci_type_id' => 'required|exists:ci_types,id',
            'asset_id' => 'nullable|exists:assets,id',
            'organization_id' => 'required|exists:organizations,id',
            'location_id' => 'nullable|exists:locations,id',
            'hostname' => 'nullable|string|max:255',
            'ip_address' => 'nullable|string|max:45',
            'mac_address' => 'nullable|string|max:50',
            'domain' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'operating_system' => 'nullable|string|max:255',
            'os_version' => 'nullable|string|max:100',
            'environment' => 'required|in:production,staging,development,dr',
            'status' => 'required|in:planned,active,maintenance,warning,down,retired,archived',
            'criticality' => 'required|in:critical,high,medium,low',
            'owner_person' => 'nullable|string|max:255',
            'responsible_unit' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'specs_key' => 'nullable|array',
            'specs_val' => 'nullable|array',
        ]);

        // Build specifications JSON
        $specifications = [];
        if ($request->has('specs_key') && is_array($request->specs_key)) {
            foreach ($request->specs_key as $index => $key) {
                if (! empty($key) && isset($request->specs_val[$index])) {
                    $specifications[$key] = $request->specs_val[$index];
                }
            }
        }
        $validated['specifications'] = $specifications;

        $ci = ConfigurationItem::create($validated);

        return redirect()->route('cmdb.show', $ci)->with('success', "Configuration Item {$ci->ci_code} berhasil dibuat.");
    }

    public function show(ConfigurationItem $cmdb): View
    {
        $cmdb->load([
            'ciType',
            'asset',
            'organization',
            'location',
            'website',
            'incidents.assignedTechnician',
            'tickets.requester',
            'risks',
            'documents',
        ]);

        $relationships = $cmdb->getAllRelationships();

        // Audit timeline for this specific CI
        $timeline = AuditLog::where('auditable_type', ConfigurationItem::class)
            ->where('auditable_id', $cmdb->id)
            ->latest('created_at')
            ->take(15)
            ->get();

        $allCis = ConfigurationItem::where('id', '!=', $cmdb->id)->orderBy('name')->get();

        return view('cmdb.show', compact('cmdb', 'relationships', 'timeline', 'allCis'));
    }

    public function edit(ConfigurationItem $cmdb): View
    {
        $ciTypes = CiType::where('is_active', true)->get();
        $organizations = Organization::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $assets = Asset::orderBy('name')->get();

        return view('cmdb.edit', compact('cmdb', 'ciTypes', 'organizations', 'locations', 'assets'));
    }

    public function update(Request $request, ConfigurationItem $cmdb): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ci_type_id' => 'required|exists:ci_types,id',
            'asset_id' => 'nullable|exists:assets,id',
            'organization_id' => 'required|exists:organizations,id',
            'location_id' => 'nullable|exists:locations,id',
            'hostname' => 'nullable|string|max:255',
            'ip_address' => 'nullable|string|max:45',
            'mac_address' => 'nullable|string|max:50',
            'domain' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'operating_system' => 'nullable|string|max:255',
            'os_version' => 'nullable|string|max:100',
            'environment' => 'required|in:production,staging,development,dr',
            'status' => 'required|in:planned,active,maintenance,warning,down,retired,archived',
            'criticality' => 'required|in:critical,high,medium,low',
            'owner_person' => 'nullable|string|max:255',
            'responsible_unit' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'specs_key' => 'nullable|array',
            'specs_val' => 'nullable|array',
        ]);

        $specifications = [];
        if ($request->has('specs_key') && is_array($request->specs_key)) {
            foreach ($request->specs_key as $index => $key) {
                if (! empty($key) && isset($request->specs_val[$index])) {
                    $specifications[$key] = $request->specs_val[$index];
                }
            }
        }
        $validated['specifications'] = $specifications;

        $cmdb->update($validated);

        return redirect()->route('cmdb.show', $cmdb)->with('success', "Data {$cmdb->ci_code} berhasil diperbarui.");
    }

    public function destroy(ConfigurationItem $cmdb): RedirectResponse
    {
        $code = $cmdb->ci_code;
        $cmdb->delete();

        return redirect()->route('cmdb.index')->with('success', "Configuration Item {$code} berhasil dihapus.");
    }

    public function relationships(Request $request): View
    {
        $query = CiRelationship::with(['sourceCi.ciType', 'targetCi.ciType']);

        if ($request->filled('type')) {
            $query->where('relationship_type', $request->type);
        }

        $relationships = $query->latest()->paginate(20)->withQueryString();
        $allCis = ConfigurationItem::orderBy('name')->get();

        return view('cmdb.relationships', compact('relationships', 'allCis'));
    }

    public function storeRelationship(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'source_ci_id' => 'required|exists:configuration_items,id|different:target_ci_id',
            'target_ci_id' => 'required|exists:configuration_items,id',
            'relationship_type' => 'required|in:depends_on,used_by,hosted_on,runs_on,connects_to,contains,uses,protected_by,managed_by,located_at,owned_by,supports,part_of',
            'description' => 'nullable|string|max:255',
        ]);

        CiRelationship::updateOrCreate(
            [
                'source_ci_id' => $validated['source_ci_id'],
                'target_ci_id' => $validated['target_ci_id'],
                'relationship_type' => $validated['relationship_type'],
            ],
            ['description' => $validated['description']]
        );

        return back()->with('success', 'Relasi CMDB berhasil dibuat / diperbarui.');
    }

    public function destroyRelationship(CiRelationship $relationship): RedirectResponse
    {
        $relationship->delete();

        return back()->with('success', 'Relasi CMDB berhasil dihapus.');
    }

    public function graph(): View
    {
        $ciTypes = CiType::where('is_active', true)->get();

        return view('cmdb.graph', compact('ciTypes'));
    }

    /**
     * JSON Data for Vis.js Topology Graph (Smart Integrated)
     */
    public function graphData(Request $request): JsonResponse
    {
        $query = ConfigurationItem::with(['ciType', 'organization', 'securityIncidents' => function($q) {
            $q->whereNotIn('workflow_status', ['closed', 'resolved', 'false_positive', 'duplicate']);
        }, 'agent']);

        if ($request->filled('type')) {
            $query->where('ci_type_id', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $cis = $query->get();
        $ciIds = $cis->pluck('id')->toArray();

        $nodes = [];
        foreach ($cis as $ci) {
            $hasIncidents = $ci->securityIncidents->count() > 0;
            $agentStatus = $ci->agent ? $ci->agent->status : 'unmanaged';
            
            // Priority styling: If it has incidents, it's compromised/red regardless of normal status
            if ($hasIncidents) {
                $borderColor = '#ef4444'; // Red
                $bgColor = '#450a0a';
                $pulse = true;
            } else {
                $pulse = false;
                $borderColor = match ($ci->status) {
                    'active' => '#10b981', // green
                    'warning' => '#f59e0b', // amber
                    'down' => '#ef4444', // red
                    'maintenance' => '#6366f1', // indigo
                    default => '#64748b',
                };
                $bgColor = '#1e293b';
            }

            // Generate smart tooltip
            $tooltip = "<strong>{$ci->name}</strong><br>Tipe: {$ci->ciType->name}<br>Status: ".strtoupper($ci->status).'<br>IP: '.($ci->ip_address ?? 'N/A');
            if ($hasIncidents) {
                $tooltip .= "<br><span style='color:#ef4444;font-weight:bold;'>⚠️ {$ci->securityIncidents->count()} Open Incidents!</span>";
            }
            if ($ci->agent) {
                $tooltip .= "<br><span style='color:#3b82f6;'>🛡️ EDR: ".strtoupper($agentStatus)."</span>";
            }

            $nodes[] = [
                'id' => $ci->id,
                'label' => "{$ci->ci_code}\n{$ci->name}" . ($hasIncidents ? "\n(⚠️ ALERT)" : ""),
                'title' => $tooltip,
                'shape' => match ($ci->ciType->code) {
                    'server' => 'box',
                    'database' => 'database',
                    'firewall', 'security_device' => 'diamond',
                    'website' => 'ellipse',
                    'router', 'switch' => 'hexagon',
                    default => 'box',
                },
                'color' => [
                    'background' => $bgColor,
                    'border' => $borderColor,
                    'highlight' => [
                        'background' => '#334155',
                        'border' => '#38bdf8',
                    ],
                ],
                'font' => [
                    'color' => $hasIncidents ? '#fca5a5' : '#f8fafc',
                    'size' => 12,
                    'face' => 'Plus Jakarta Sans',
                ],
                'borderWidth' => $hasIncidents ? 4 : 2,
                'margin' => 10,
                'ci_code' => $ci->ci_code,
                'ci_name' => $ci->name,
                'status' => $ci->status,
                'ip' => $ci->ip_address,
                'has_incidents' => $hasIncidents,
                'incident_count' => $ci->securityIncidents->count(),
                'agent_status' => $agentStatus,
                'pulse' => $pulse,
                'url' => route('cmdb.show', $ci),
            ];
        }

        $relationships = CiRelationship::whereIn('source_ci_id', $ciIds)
            ->whereIn('target_ci_id', $ciIds)
            ->get();

        $edges = [];
        foreach ($relationships as $rel) {
            $sourceCi = $cis->firstWhere('id', $rel->source_ci_id);
            $targetCi = $cis->firstWhere('id', $rel->target_ci_id);
            
            // If both source and target are active, animate data flow
            $isActiveFlow = ($sourceCi && $sourceCi->status === 'active' && $targetCi && $targetCi->status === 'active');
            
            $edges[] = [
                'from' => $rel->source_ci_id,
                'to' => $rel->target_ci_id,
                'label' => $rel->relationship_type,
                'arrows' => 'to',
                'color' => ['color' => $isActiveFlow ? '#10b981' : '#64748b', 'highlight' => '#38bdf8'],
                'font' => ['color' => $isActiveFlow ? '#10b981' : '#94a3b8', 'size' => 10, 'align' => 'horizontal'],
                'smooth' => ['type' => 'cubicBezier'],
                'dashes' => $isActiveFlow ? true : false,
                'is_active_flow' => $isActiveFlow, // For frontend animation flag
            ];
        }

        return response()->json([
            'nodes' => $nodes,
            'edges' => $edges,
        ]);
    }
}
