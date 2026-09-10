<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Website;
use App\Models\WebsiteCheckLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function websites(): View
    {
        $websites = Website::with(['configurationItem.ciType', 'organization'])
            ->latest('last_checked_at')
            ->paginate(15);

        $stats = [
            'total' => Website::count(),
            'up' => Website::where('current_status', 'up')->count(),
            'down' => Website::where('current_status', 'down')->count(),
            'ssl_warning' => Website::whereIn('ssl_status', ['expiring_soon', 'expired', 'invalid'])->count(),
            'avg_response_time' => round(Website::where('current_status', 'up')->avg('response_time_ms') ?? 0),
        ];

        return view('monitoring.websites', compact('websites', 'stats'));
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
