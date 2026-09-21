<?php

namespace App\Http\Controllers;

use App\Models\ConfigurationItem;
use App\Models\Incident;
use App\Models\Organization;
use App\Models\Website;
use App\Models\WebsiteCheckLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function websites(Request $request): View
    {
        $query = Website::with(['configurationItem.ciType', 'organization'])
            ->latest('last_checked_at');

        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            if ($status === 'ssl_warning') {
                $query->whereIn('ssl_status', ['expiring_soon', 'expired', 'invalid']);
            } else {
                $query->where('current_status', $status);
            }
        }

        if ($opd = $request->input('organization_id')) {
            $query->where('organization_id', $opd);
        }

        $websites = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Website::count(),
            'up' => Website::where('current_status', 'up')->count(),
            'down' => Website::where('current_status', 'down')->count(),
            'ssl_warning' => Website::whereIn('ssl_status', ['expiring_soon', 'expired', 'invalid'])->count(),
            'avg_response_time' => round(Website::where('current_status', 'up')->avg('response_time_ms') ?? 0),
        ];

        $organizations = Organization::orderBy('name')->get();
        $configurationItems = ConfigurationItem::orderBy('name')->get();

        return view('monitoring.websites', compact('websites', 'stats', 'organizations', 'configurationItems'));
    }

    public function storeWebsite(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'organization_id' => 'required|exists:organizations,id',
            'ci_id' => 'required|exists:configuration_items,id',
        ]);

        $website = Website::create($validated);

        return back()->with('success', "Website {$website->name} berhasil ditambahkan ke monitoring.");
    }

    public function update(Request $request, Website $website): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'organization_id' => 'required|exists:organizations,id',
            'ci_id' => 'required|exists:configuration_items,id',
        ]);

        $website->update($validated);

        return back()->with('success', "Website {$website->name} berhasil diperbarui.");
    }

    public function destroy(Website $website): RedirectResponse
    {
        $name = $website->name;
        $website->delete();

        return back()->with('success', "Website {$name} berhasil dihapus dari monitoring.");
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_import_website.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['name', 'url', 'organization_id', 'ci_id']);
            
            $org = Organization::first();
            $ci = ConfigurationItem::first();
            
            fputcsv($file, [
                'Website Resmi Dummy',
                'https://example.ciamiskab.go.id',
                $org ? $org->id : '1',
                $ci ? $ci->id : '1'
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function export(Request $request)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="laporan_website_monitoring.csv"',
        ];

        $callback = function() use ($request) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Nama Website', 'URL', 'OPD Pengelola', 'CI Terkait', 
                'Status Saat Ini', 'HTTP Status', 'Response Time (ms)', 
                'Status SSL', 'Masa Aktif SSL (Hari)', 'Terakhir Dicek'
            ]);

            $query = Website::with(['configurationItem', 'organization'])->latest('last_checked_at');

            if ($search = $request->input('search')) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('url', 'like', "%{$search}%");
                });
            }
            if ($status = $request->input('status')) {
                if ($status === 'ssl_warning') {
                    $query->whereIn('ssl_status', ['expiring_soon', 'expired', 'invalid']);
                } else {
                    $query->where('current_status', $status);
                }
            }
            if ($opd = $request->input('organization_id')) {
                $query->where('organization_id', $opd);
            }

            $query->chunk(100, function ($websites) use ($file) {
                foreach ($websites as $site) {
                    $sslDays = null;
                    if ($site->ssl_expires_at) {
                        $sslDays = round(now()->diffInDays($site->ssl_expires_at, false));
                    }

                    fputcsv($file, [
                        $site->name,
                        $site->url,
                        $site->organization ? $site->organization->name : '',
                        $site->configurationItem ? $site->configurationItem->ci_code : '',
                        strtoupper($site->current_status),
                        $site->http_status_code,
                        $site->response_time_ms,
                        strtoupper($site->ssl_status),
                        $sslDays,
                        $site->last_checked_at ? $site->last_checked_at->format('Y-m-d H:i:s') : ''
                    ]);
                }
            });
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        
        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($header) == count($row)) {
                $data = array_combine($header, $row);
                if (!empty($data['name']) && !empty($data['url']) && !empty($data['organization_id']) && !empty($data['ci_id'])) {
                    Website::create([
                        'name' => $data['name'],
                        'url' => $data['url'],
                        'organization_id' => $data['organization_id'],
                        'ci_id' => $data['ci_id'],
                    ]);
                    $count++;
                }
            }
        }
        fclose($handle);

        return back()->with('success', "Berhasil mengimpor {$count} website ke dalam monitoring.");
    }

    public function check(Website $website): RedirectResponse
    {
        $startTime = microtime(true);
        $status = 'down';
        $httpCode = null;
        $errorMessage = null;
        $sslValid = false;
        $sslDaysLeft = null;
        $sslStatus = 'unknown';
        $sslIssuer = null;
        $sslExpiresAt = null;

        try {
            $response = Http::timeout(6)->withoutVerifying()->get($website->url);
            $responseTimeMs = (int) round((microtime(true) - $startTime) * 1000);
            $httpCode = $response->status();

            if ($response->successful() || $response->redirect()) {
                $status = 'up';
            } else {
                $status = 'down';
                $errorMessage = "HTTP Status {$httpCode}";
            }
        } catch (\Throwable $e) {
            $responseTimeMs = (int) round((microtime(true) - $startTime) * 1000);
            $errorMessage = $e->getMessage();
            $status = 'down';
        }

        // Basic SSL check if URL starts with https
        if (str_starts_with($website->url, 'https://')) {
            try {
                $parsed = parse_url($website->url);
                $host = $parsed['host'] ?? null;
                if ($host) {
                    $context = stream_context_create([
                        'ssl' => ['capture_peer_cert' => true, 'verify_peer' => false, 'verify_peer_name' => false],
                    ]);
                    $client = @stream_socket_client("ssl://{$host}:443", $errno, $errstr, 4, STREAM_CLIENT_CONNECT, $context);
                    if ($client) {
                        $params = stream_context_get_params($client);
                        $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate']);
                        if ($cert && isset($cert['validTo_time_t'])) {
                            $sslExpiresAt = date('Y-m-d H:i:s', $cert['validTo_time_t']);
                            $daysLeft = (int) round(($cert['validTo_time_t'] - time()) / 86400);
                            $sslDaysLeft = $daysLeft;
                            $sslValid = $daysLeft > 0;
                            $sslStatus = $daysLeft <= 14 ? ($daysLeft <= 0 ? 'expired' : 'expiring_soon') : 'valid';
                            $sslIssuer = $cert['issuer']['O'] ?? $cert['issuer']['CN'] ?? "Let's Encrypt";
                        }
                        fclose($client);
                    }
                }
            } catch (\Throwable $e) {
                // SSL check failed
            }
        }

        // Record check log
        WebsiteCheckLog::create([
            'website_id' => $website->id,
            'status' => $status,
            'http_status_code' => $httpCode,
            'response_time_ms' => $responseTimeMs,
            'error_message' => $errorMessage,
            'ssl_valid' => $sslValid,
            'ssl_days_left' => $sslDaysLeft,
            'checked_at' => now(),
        ]);

        $statusChanged = $website->current_status !== $status;

        $website->update([
            'current_status' => $status,
            'http_status_code' => $httpCode,
            'response_time_ms' => $responseTimeMs,
            'ssl_status' => $sslStatus,
            'ssl_issuer' => $sslIssuer ?? $website->ssl_issuer,
            'ssl_expires_at' => $sslExpiresAt ?? $website->ssl_expires_at,
            'last_checked_at' => now(),
            'last_status_change_at' => $statusChanged ? now() : $website->last_status_change_at,
        ]);

        // AUTOMATED INCIDENT RULE (Prompt Section 13):
        // If website DOWN -> Auto create Incident linked to CI
        if ($status === 'down') {
            $existingIncident = Incident::where('ci_id', $website->ci_id)
                ->where('source', 'monitoring')
                ->where('status', '!=', 'closed')
                ->first();

            if (! $existingIncident) {
                Incident::create([
                    'title' => "Website DOWN Terdeteksi Monitoring: {$website->name}",
                    'source' => 'monitoring',
                    'ci_id' => $website->ci_id,
                    'organization_id' => $website->organization_id,
                    'priority' => 'high',
                    'status' => 'open',
                    'impact_description' => "Website {$website->url} gagal diakses melalui pengecekan otomatis. Pesan: {$errorMessage}",
                    'detected_at' => now(),
                ]);
            }
        }

        return back()->with('success', "Pemeriksaan untuk {$website->name} selesai. Status: ".strtoupper($status));
    }
}
