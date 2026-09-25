<?php

namespace App\Http\Controllers;

use App\Models\ConfigurationItem;
use App\Models\Organization;
use App\Models\PublicIncidentReport;
use App\Models\SecurityIncident;
use App\Models\SecurityIncidentAuditLog;
use App\Models\SecurityIncidentEvidence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicIncidentReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = PublicIncidentReport::with(['reviewer', 'securityIncident']);

        $status = $request->query('status', 'all');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('reporter_name', 'like', "%{$search}%")
                    ->orWhere('whatsapp_from', 'like', "%{$search}%")
                    ->orWhere('affected_asset', 'like', "%{$search}%")
                    ->orWhere('incident_type', 'like', "%{$search}%");
            });
        }

        $reports = $query->latest()->paginate(15)->withQueryString();

        $counts = [
            'all' => PublicIncidentReport::count(),
            'pending_review' => PublicIncidentReport::where('status', 'pending_review')->count(),
            'verified' => PublicIncidentReport::where('status', 'verified')->count(),
            'rejected' => PublicIncidentReport::where('status', 'rejected')->count(),
        ];

        return view('security.public-incidents.index', compact('reports', 'counts', 'status'));
    }

    public function show(int $id): View
    {
        $report = PublicIncidentReport::with(['reviewer', 'securityIncident.organization'])->findOrFail($id);
        $organizations = Organization::orderBy('name')->get();
        $cis = ConfigurationItem::orderBy('name')->get();

        return view('security.public-incidents.show', compact('report', 'organizations', 'cis'));
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $report = PublicIncidentReport::findOrFail($id);

        $validated = $request->validate([
            'review_notes' => 'required|string|max:1000',
        ]);

        $report->update([
            'status' => 'rejected',
            'review_notes' => $validated['review_notes'],
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('security.public-incidents.show', $report->id)
            ->with('success', 'Laporan publik telah ditandai DITOLAK (Tidak Valid). Status pelapor telah diperbarui.');
    }

    public function escalate(Request $request, int $id): RedirectResponse
    {
        $report = PublicIncidentReport::findOrFail($id);

        if ($report->status === 'verified' && $report->security_incident_id) {
            return redirect()->route('security.incidents.show', $report->security_incident_id)
                ->with('info', 'Laporan ini sudah divalidasi sebelumnya.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'incident_type' => 'required|in:malware,phishing,defacement,unauthorized_access,account_compromise,data_exposure,vulnerability,website_attack,network_attack,other',
            'severity' => 'required|in:critical,high,medium,low',
            'organization_id' => 'required|exists:organizations,id',
            'ci_id' => 'nullable|exists:configuration_items,id',
            'review_notes' => 'nullable|string|max:1000',
        ]);

        $securityIncident = DB::transaction(function () use ($report, $validated) {
            $incident = SecurityIncident::create([
                'title' => $validated['title'],
                'incident_type' => $validated['incident_type'],
                'severity' => $validated['severity'],
                'organization_id' => $validated['organization_id'],
                'ci_id' => $validated['ci_id'] ?? null,
                'reporter_id' => Auth::id(),
                'workflow_status' => 'investigating',
                'description' => "Laporan Masuk via {$report->source} (Tiket: {$report->ticket_number})\n".
                    "Pelapor: {$report->reporter_name} ({$report->whatsapp_from})\n\n".
                    "Kronologi:\n".($report->chronology ?: '-')."\n\n".
                    "Dampak:\n".($report->impact ?: '-'),
                'impact_summary' => $report->impact,
                'target_type' => 'Domain/URL/Asset',
                'target_value' => $report->affected_asset,
                'detected_at' => $report->incident_time ?? now(),
                'detection_rule' => "Inbound Report ({$report->source})",
            ]);

            // Copy attachments into SecurityIncidentEvidence
            if (is_array($report->attachments)) {
                foreach ($report->attachments as $att) {
                    $evidencePath = $att['path'] ?? $att['url'] ?? null;
                    if ($evidencePath) {
                        SecurityIncidentEvidence::create([
                            'incident_id' => $incident->id,
                            'type' => ! empty($att['mimeType']) && str_starts_with($att['mimeType'], 'image/') ? 'image' : 'file',
                            'title' => $att['filename'] ?? 'Bukti Laporan WhatsApp',
                            'description' => $att['note'] ?? "Bukti pendukung dari pelapor ({$report->whatsapp_from})",
                            'file_path' => $evidencePath,
                            'mime_type' => $att['mimeType'] ?? null,
                            'file_size' => $att['sizeBytes'] ?? 0,
                            'collected_by' => Auth::id(),
                            'collected_at' => now(),
                        ]);
                    }
                }
            }

            SecurityIncidentAuditLog::create([
                'incident_id' => $incident->id,
                'user_id' => Auth::id(),
                'action' => 'escalated_from_public_report',
                'new_value' => "Insiden dibuat dari verifikasi laporan publik nomor {$report->ticket_number} ({$report->source})",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $report->update([
                'status' => 'verified',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'security_incident_id' => $incident->id,
                'review_notes' => $validated['review_notes'] ?? 'Divalidasi dan dinaikkan statusnya menjadi insiden keamanan aktif.',
            ]);

            return $incident;
        });

        return redirect()->route('security.incidents.show', $securityIncident->id)
            ->with('success', "Laporan publik berhasil divalidasi! Insiden Keamanan resmi [{$securityIncident->incident_code}] telah aktif dan siap ditangani.");
    }
}
