<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Asset;
use App\Models\ConfigurationItem;
use App\Models\Incident;
use App\Models\Risk;
use App\Models\SecurityIncident;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Website;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiController extends Controller
{
    /**
     * POST /api/v1/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->login)
            ->orWhere('username', $request->login)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Kredensial yang diberikan tidak valid.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun pengguna sedang dinonaktifkan.',
            ], 403);
        }

        $token = $user->createToken('sikandi-api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'organization' => $user->organization?->name,
            ],
        ]);
    }

    /**
     * GET /api/v1/assets
     */
    public function assets(Request $request): JsonResponse
    {
        $query = Asset::with(['category', 'organization', 'location']);

        if ($request->filled('status')) {
            $query->where('lifecycle_status', $request->status);
        }
        if ($request->filled('category_id')) {
            $query->where('asset_category_id', $request->category_id);
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(20),
        ]);
    }

    /**
     * GET /api/v1/assets/{id}
     */
    public function assetDetail(int $id): JsonResponse
    {
        $asset = Asset::with(['category', 'organization', 'location', 'assignedTo', 'configurationItems', 'maintenances'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $asset,
        ]);
    }

    /**
     * GET /api/v1/ci
     */
    public function cis(Request $request): JsonResponse
    {
        $query = ConfigurationItem::with(['ciType', 'organization', 'asset']);

        if ($request->filled('type_id')) {
            $query->where('ci_type_id', $request->type_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('criticality')) {
            $query->where('criticality', $request->criticality);
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(20),
        ]);
    }

    /**
     * GET /api/v1/ci/{id}
     */
    public function ciDetail(int $id): JsonResponse
    {
        $ci = ConfigurationItem::with(['ciType', 'organization', 'asset', 'location', 'tickets', 'incidents', 'securityIncidents'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $ci,
            'relationships' => $ci->getAllRelationships(),
        ]);
    }

    /**
     * GET /api/v1/ci/{id}/relationships
     */
    public function ciRelationships(int $id): JsonResponse
    {
        $ci = ConfigurationItem::findOrFail($id);

        return response()->json([
            'success' => true,
            'ci' => [
                'id' => $ci->id,
                'ci_code' => $ci->ci_code,
                'name' => $ci->name,
            ],
            'outbound' => $ci->outboundRelations()->with('targetCi.ciType')->get(),
            'inbound' => $ci->inboundRelations()->with('sourceCi.ciType')->get(),
        ]);
    }

    /**
     * GET /api/v1/tickets
     */
    public function tickets(Request $request): JsonResponse
    {
        $query = Ticket::with(['requester', 'organization', 'service', 'assignedTechnician', 'configurationItem']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest()->paginate(20),
        ]);
    }

    /**
     * POST /api/v1/tickets
     */
    public function createTicket(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'ci_id' => 'nullable|exists:configuration_items,id',
            'asset_id' => 'nullable|exists:assets,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:critical,high,medium,low',
        ]);

        $validated['requester_id'] = Auth::id() ?? 1;
        $validated['status'] = 'open';

        $ticket = Ticket::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tiket berhasil diajukan.',
            'data' => $ticket->load(['service', 'configurationItem']),
        ], 201);
    }

    /**
     * GET /api/v1/incidents
     */
    public function incidents(Request $request): JsonResponse
    {
        $query = Incident::with(['configurationItem', 'asset', 'organization', 'assignedTechnician']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest()->paginate(20),
        ]);
    }

    /**
     * POST /api/v1/incidents
     */
    public function createIncident(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'source' => 'required|in:service_desk,monitoring,security_monitoring,manual_report',
            'ci_id' => 'nullable|exists:configuration_items,id',
            'asset_id' => 'nullable|exists:assets,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'priority' => 'required|in:critical,high,medium,low',
            'impact_description' => 'nullable|string',
            'root_cause' => 'nullable|string',
        ]);

        $validated['status'] = 'open';
        $validated['detected_at'] = now();

        $incident = Incident::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Laporan insiden berhasil dicatat.',
            'data' => $incident->load(['configurationItem', 'organization']),
        ], 201);
    }

    /**
     * GET /api/v1/websites/status
     */
    public function websitesStatus(): JsonResponse
    {
        $websites = Website::with('organization')
            ->select('id', 'name', 'url', 'organization_id', 'current_status', 'http_status_code', 'response_time_ms', 'ssl_status', 'last_checked_at')
            ->get();

        return response()->json([
            'success' => true,
            'summary' => [
                'total' => $websites->count(),
                'up' => $websites->where('current_status', 'up')->count(),
                'down' => $websites->where('current_status', 'down')->count(),
            ],
            'data' => $websites,
        ]);
    }

    /**
     * GET /api/v1/security-incidents
     */
    public function securityIncidents(Request $request): JsonResponse
    {
        $query = SecurityIncident::with(['organization', 'configurationItem', 'reporter', 'assignedLead']);

        if ($request->filled('type')) {
            $query->where('incident_type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('workflow_status', $request->status);
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest()->paginate(20),
        ]);
    }

    /**
     * GET /api/v1/assessments/summary
     */
    public function assessmentsSummary(): JsonResponse
    {
        $assessments = Assessment::with('organization')
            ->latest('updated_at')
            ->get();

        return response()->json([
            'success' => true,
            'average_compliance_score' => round(Assessment::avg('compliance_score') ?? 0, 1),
            'average_risk_score' => round(Assessment::avg('risk_score') ?? 0, 1),
            'total_assessments' => $assessments->count(),
            'data' => $assessments,
        ]);
    }

    /**
     * GET /api/v1/risks
     */
    public function risks(Request $request): JsonResponse
    {
        $query = Risk::with(['organization', 'configurationItem', 'owner', 'treatments']);

        if ($request->filled('level')) {
            $query->where('risk_level', $request->level);
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest()->paginate(20),
        ]);
    }
}
