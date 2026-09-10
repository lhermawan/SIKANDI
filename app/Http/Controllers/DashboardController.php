<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\ConfigurationItem;
use App\Models\Incident;
use App\Models\Organization;
use App\Models\Risk;
use App\Models\SecurityIncident;
use App\Models\Ticket;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        // 1. IT Technician Dashboard
        if ($user->hasRole('IT Technician')) {
            $assignedTickets = Ticket::where('assigned_technician_id', $user->id)
                ->whereIn('status', ['assigned', 'in_progress'])
                ->with(['organization', 'configurationItem', 'service'])
                ->latest()
                ->take(5)
                ->get();

            $activeIncidents = Incident::where('assigned_technician_id', $user->id)
                ->whereIn('status', ['open', 'investigation', 'in_progress'])
                ->with(['configurationItem', 'organization'])
                ->latest()
                ->take(5)
                ->get();

            $downWebsites = Website::where('current_status', '!=', 'up')
                ->with('organization')
                ->take(5)
                ->get();

            $stats = [
                'my_tickets' => Ticket::where('assigned_technician_id', $user->id)->whereIn('status', ['assigned', 'in_progress'])->count(),
                'my_incidents' => Incident::where('assigned_technician_id', $user->id)->where('status', '!=', 'closed')->count(),
                'total_ci_active' => ConfigurationItem::where('status', 'active')->count(),
                'websites_down' => Website::where('current_status', 'down')->count(),
            ];

            return view('dashboard.technician', compact('stats', 'assignedTickets', 'activeIncidents', 'downWebsites'));
        }

        // 2. OPD User Dashboard
        if ($user->hasRole('OPD User')) {
            $org = $user->organization;
            $myTickets = Ticket::where('organization_id', $org?->id)
                ->with(['service', 'assignedTechnician'])
                ->latest()
                ->take(5)
                ->get();

            $myAssets = Asset::where('organization_id', $org?->id)
                ->with(['category', 'location'])
                ->latest()
                ->take(5)
                ->get();

            $myCis = ConfigurationItem::where('organization_id', $org?->id)
                ->with('ciType')
                ->latest()
                ->take(5)
                ->get();

            $stats = [
                'total_assets' => Asset::where('organization_id', $org?->id)->count(),
                'total_ci' => ConfigurationItem::where('organization_id', $org?->id)->count(),
                'open_tickets' => Ticket::where('organization_id', $org?->id)->whereIn('status', ['open', 'assigned', 'in_progress'])->count(),
                'compliance_score' => $org?->assessments()->latest()->value('compliance_score') ?? 0,
            ];

            return view('dashboard.opd', compact('stats', 'myTickets', 'myAssets', 'myCis', 'org'));
        }

        // 3. Management Dashboard
        if ($user->hasRole('Management')) {
            $stats = [
                'total_assets' => Asset::count(),
                'total_ci' => ConfigurationItem::count(),
                'total_websites' => Website::count(),
                'open_incidents' => Incident::where('status', '!=', 'closed')->count(),
                'high_risks' => Risk::whereIn('risk_level', ['critical', 'high'])->count(),
                'ikasandi_avg_score' => round(Assessment::avg('compliance_score') ?? 0, 1),
                'total_opd' => Organization::count(),
                'assessed_opd' => Organization::whereHas('assessments')->count(),
            ];

            $criticalRisks = Risk::whereIn('risk_level', ['critical', 'high'])
                ->with(['organization', 'configurationItem'])
                ->latest()
                ->take(5)
                ->get();

            return view('dashboard.management', compact('stats', 'criticalRisks'));
        }

        // 4. Default: Super Admin & Admin Persandian
        $stats = [
            'total_ci' => ConfigurationItem::count(),
            'total_assets' => Asset::count(),
            'total_opd' => Organization::count(),
            'open_tickets' => Ticket::whereIn('status', ['open', 'assigned', 'in_progress'])->count(),
            'active_incidents' => Incident::where('status', '!=', 'closed')->count(),
            'monitored_websites' => Website::count(),
            'websites_up' => Website::where('current_status', 'up')->count(),
            'websites_down' => Website::where('current_status', 'down')->count(),
            'security_incidents' => SecurityIncident::where('workflow_status', '!=', 'closed')->count(),
            'critical_risks' => Risk::where('risk_level', 'critical')->count(),
        ];

        $recentCis = ConfigurationItem::with(['ciType', 'organization'])->latest()->take(5)->get();
        $recentTickets = Ticket::with(['requester', 'organization', 'service'])->latest()->take(5)->get();
        $recentIncidents = Incident::with(['configurationItem', 'assignedTechnician'])->latest()->take(5)->get();
        $recentAuditLogs = AuditLog::latest('created_at')->take(6)->get();

        return view('dashboard.admin', compact('stats', 'recentCis', 'recentTickets', 'recentIncidents', 'recentAuditLogs'));
    }
}
