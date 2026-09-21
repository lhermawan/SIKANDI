<?php

namespace App\Http\Controllers;

use App\Jobs\EnrichIpReputationJob;
use App\Models\Assessment;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\ConfigurationItem;
use App\Models\Incident;
use App\Models\IpReputation;
use App\Models\Organization;
use App\Models\Risk;
use App\Models\SecurityEvent;
use App\Models\SecurityIncident;
use App\Models\SecurityIncidentResponse;
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

        // 4. Default: Super Admin & Admin Persandian (SOC Dashboard)
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
            'high_severity_alerts' => SecurityIncident::where('severity', 'high')->orWhere('severity', 'critical')->where('workflow_status', '!=', 'closed')->count(),
            'total_security_events' => SecurityEvent::count(),
        ];

        // SOC Analytics: Incident Trend (Last 7 Days)
        $last7Days = collect(range(6, 0))->map(fn ($day) => now()->subDays($day)->format('Y-m-d'));

        $trendData = SecurityIncident::where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $incidentTrend = $last7Days->map(fn ($date) => [
            'date' => $date,
            'total' => $trendData->get($date, 0),
        ]);

        // SOC Analytics: Incidents by Severity
        $severityData = SecurityIncident::selectRaw('severity, COUNT(*) as total')
            ->groupBy('severity')
            ->pluck('total', 'severity');

        $severityChart = [
            'Critical' => $severityData->get('critical', 0),
            'High' => $severityData->get('high', 0),
            'Medium' => $severityData->get('medium', 0),
            'Low' => $severityData->get('low', 0),
        ];

        // SOC Analytics: Top 5 Attacker IPs
        $topAttackers = SecurityEvent::selectRaw('source_ip, COUNT(*) as total_events')
            ->whereNotNull('source_ip')
            ->where('source_ip', '!=', '')
            ->where('source_ip', '!=', '127.0.0.1')
            ->groupBy('source_ip')
            ->orderByDesc('total_events')
            ->take(5)
            ->get();

        // Sambungkan dengan Threat Intelligence dan jalankan pengecekan Background jika belum ada cache
        foreach ($topAttackers as $attacker) {
            $rep = IpReputation::where('ip_address', $attacker->source_ip)->first();
            if (! $rep) {
                // Dispatch Job agar ditarik oleh Queue Worker, UI tetap instan
                EnrichIpReputationJob::dispatch($attacker->source_ip);
            }
            $attacker->reputation = $rep;

            // Cek apakah IP ini sudah ada di antrean blokir atau sudah diblokir
            $blockResponse = SecurityIncidentResponse::where('action', 'block_ip')
                ->where('description', 'like', "%{$attacker->source_ip}%")
                ->whereIn('status', ['pending', 'executed'])
                ->first();

            $attacker->block_status = $blockResponse ? $blockResponse->status : null;
        }

        $recentCis = ConfigurationItem::with(['ciType', 'organization'])->latest()->take(5)->get();
        $recentTickets = Ticket::with(['requester', 'organization', 'service'])->latest()->take(5)->get();
        $recentIncidents = Incident::with(['configurationItem', 'assignedTechnician'])->latest()->take(5)->get();
        $recentAuditLogs = AuditLog::latest('created_at')->take(6)->get();

        // Rekomendasi Tindakan (Pending SOC Actions)
        $pendingActions = SecurityIncidentResponse::where('status', 'pending')
            ->with('incident')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.admin', compact(
            'stats', 'recentCis', 'recentTickets', 'recentIncidents',
            'recentAuditLogs', 'incidentTrend', 'severityChart', 'topAttackers', 'pendingActions'
        ));
    }

    public function threatActors(Request $request)
    {
        $query = SecurityEvent::selectRaw('source_ip, COUNT(*) as total_events, GROUP_CONCAT(DISTINCT hostname SEPARATOR ", ") as targeted_agents, GROUP_CONCAT(DISTINCT event_type SEPARATOR ", ") as event_types, MAX(created_at) as last_seen')
            ->whereNotNull('source_ip')
            ->where('source_ip', '!=', '')
            ->where('source_ip', '!=', '127.0.0.1')
            ->where('source_ip', '!=', 'N/A');

        if ($request->filled('search')) {
            $query->where('source_ip', 'like', "%{$request->search}%");
        }

        $topAttackers = $query->groupBy('source_ip')
            ->orderByRaw('last_seen DESC') // Mengurutkan yang terbaru/scanning
            ->paginate(20)
            ->withQueryString();

        // Separate whitelisted and non-whitelisted to custom sort, or handle in loop
        foreach ($topAttackers as $attacker) {
            $rep = IpReputation::where('ip_address', $attacker->source_ip)->first();
            if (! $rep) {
                EnrichIpReputationJob::dispatch($attacker->source_ip);
            }
            $attacker->reputation = $rep;

            $blockResponse = SecurityIncidentResponse::where('action', 'block_ip')
                ->where('description', 'like', "%{$attacker->source_ip}%")
                ->whereIn('status', ['pending', 'executed'])
                ->first();

            $attacker->block_status = $blockResponse ? $blockResponse->status : null;

            // Check if blocked by fail2ban
            if (! $attacker->block_status) {
                $f2b = SecurityEvent::where('source_ip', $attacker->source_ip)
                    ->whereIn('event_type', ['FAIL2BAN_BAN', 'FAIL2BAN_UNBAN'])
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($f2b && $f2b->event_type === 'FAIL2BAN_BAN') {
                    $attacker->block_status = 'fail2ban';
                }
            }
        }

        // We can sort the collection after pagination so that unblocked ones appear on top of blocked ones
        $topAttackers->setCollection(
            $topAttackers->getCollection()->sortBy(function ($attacker) {
                // Return 0 if not blocked, 1 if pending, 2 if executed. (To make unblocked top)
                if ($attacker->block_status === 'executed') {
                    return 2;
                }
                if ($attacker->block_status === 'pending') {
                    return 1;
                }

                return 0;
            })->values()
        );

        return view('security.threat-actors', compact('topAttackers'));
    }

    public function whitelistIp(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
        ]);

        $ip = $request->ip_address;

        $reputation = IpReputation::firstOrCreate(
            ['ip_address' => $ip],
            ['is_public' => true]
        );

        $reputation->is_whitelisted = true;
        $reputation->save();

        return redirect()->back()->with('success', "IP {$ip} berhasil dimasukkan ke daftar Whitelist. IP ini tidak akan dicurigai lagi.");
    }

    public function unbanIp(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
        ]);

        $ip = $request->ip_address;

        // Broadcast perintah unban_ip ke seluruh agen
        $agents = \App\Models\Agent::all();
        foreach ($agents as $agent) {
            $agent->commands()->create([
                'action' => 'unban_ip',
                'paths' => [$ip], // Kita gunakan field paths untuk menyimpan target IP
                'status' => 'pending'
            ]);
        }

        // Jika IP ini pernah diblokir via SIKANDI SOC (status: executed/pending),
        // tandai responsenya sebagai resolved atau update deskripsi
        SecurityIncidentResponse::where('action', 'block_ip')
            ->where('description', 'like', "%{$ip}%")
            ->whereIn('status', ['pending', 'executed'])
            ->update([
                'status' => 'resolved',
                'performed_by' => Auth::id(),
                'description' => "Blokir dicabut secara manual (UNBAN). Target IP: {$ip}"
            ]);

        return redirect()->back()->with('success', "Perintah UNBAN darurat untuk IP {$ip} telah disiarkan ke seluruh agen. Blokir IPTables/Fail2ban akan segera dicabut.");
    }

    public function socApprovals(Request $request)
    {
        $query = SecurityIncidentResponse::where('status', 'pending')
            ->with('incident');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('incident', function ($q2) use ($search) {
                        $q2->where('incident_code', 'like', "%{$search}%")
                            ->orWhere('title', 'like', "%{$search}%")
                            ->orWhere('source_ip', 'like', "%{$search}%");
                    });
            });
        }

        $pendingActions = $query->latest()
            ->paginate(20)
            ->withQueryString();

        return view('security.approvals', compact('pendingActions'));
    }

    public function draftQuickBlockBulk(Request $request)
    {
        $request->validate([
            'ips' => 'required|array',
            'ips.*' => 'ip',
        ]);

        $addedCount = 0;

        foreach ($request->ips as $ip) {
            // Check globally if already drafted/blocked
            $exists = SecurityIncidentResponse::where('action', 'block_ip')
                ->where('description', 'like', "%{$ip}%")
                ->whereIn('status', ['pending', 'executed'])
                ->exists();

            if (! $exists) {
                $incident = SecurityIncident::where('source_ip', $ip)->first();
                if (! $incident) {
                    $incident = SecurityIncident::create([
                        'title' => 'Tindakan Proaktif (Bulk): Threat Intel IP '.$ip,
                        'incident_type' => 'malware',
                        'severity' => 'high',
                        'workflow_status' => 'investigation',
                        'source_ip' => $ip,
                        'description' => 'Insiden dibuat otomatis dari Bulk Action Dashboard SOC untuk menindaklanjuti IP berbahaya.',
                        'organization_id' => Organization::first()->id ?? 1,
                        'reporter_id' => Auth::id() ?? 1,
                        'first_seen_at' => now(),
                        'last_seen_at' => now(),
                    ]);
                }

                $incident->responses()->create([
                    'action' => 'block_ip',
                    'description' => 'Memblokir IP Address '.$ip.' di firewall/iptables.',
                    'status' => 'pending',
                ]);
                $addedCount++;
            }
        }

        return back()->with('success', "Berhasil memasukkan $addedCount IP ke antrean persetujuan (HitL).");
    }

    public function draftQuickBlock(Request $request)
    {
        $request->validate(['ip' => 'required|ip']);
        $ip = $request->ip;

        // Cari insiden terkait IP ini, atau buat baru jika tidak ada
        $incident = SecurityIncident::where('source_ip', $ip)->first();

        if (! $incident) {
            $incident = SecurityIncident::create([
                'title' => 'Tindakan Proaktif: Threat Intel IP '.$ip,
                'incident_type' => 'malware',
                'severity' => 'high',
                'workflow_status' => 'investigation',
                'source_ip' => $ip,
                'description' => 'Insiden dibuat otomatis dari Dashboard SOC untuk menindaklanjuti IP berbahaya (Malicious) berdasarkan laporan AbuseIPDB.',
                'organization_id' => Organization::first()->id ?? 1,
                'reporter_id' => Auth::id(),
                'first_seen_at' => now(),
                'last_seen_at' => now(),
            ]);
        }

        // Cek apakah sudah ada draft/eksekusi untuk IP ini
        $exists = $incident->responses()->where('action', 'block_ip')
            ->where('description', 'like', "%$ip%")
            ->whereIn('status', ['pending', 'executed'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Tindakan blokir untuk IP '.$ip.' sudah ada di antrean atau telah dieksekusi.');
        }

        $incident->responses()->create([
            'action' => 'block_ip',
            'description' => 'Blokir IP Address '.$ip.' (Threat Intel: Malicious)',
            'status' => 'pending',
            'performed_by' => null,
        ]);

        return back()->with('success', 'Draft blokir untuk IP '.$ip.' berhasil ditambahkan ke antrean Menunggu Eksekusi.');
    }
}
