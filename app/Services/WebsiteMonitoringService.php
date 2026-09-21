<?php

namespace App\Services;

use App\Models\Website;
use App\Models\WebsiteCheckLog;
use App\Models\Incident;
use Illuminate\Support\Facades\Http;

class WebsiteMonitoringService
{
    public function check(Website $website): void
    {
        $startTime = microtime(true);
        $status = 'down';
        $httpCode = null;
        $errorMessage = null;

        try {
            $response = Http::withoutVerifying()->timeout(10)->get($website->url);
            $httpCode = $response->status();
            $status = $response->successful() ? 'up' : 'down';
            if (! $response->successful()) {
                $errorMessage = "HTTP Error: {$httpCode}";
            }
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
        }

        $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);
        
        // Resolve IP Address
        $ipAddress = null;
        $host = parse_url($website->url, PHP_URL_HOST);
        if ($host) {
            $resolvedIp = gethostbyname($host);
            if ($resolvedIp !== $host) {
                $ipAddress = $resolvedIp;
            }
        }

        // SSL Check (Jika HTTPS)
        $sslValid = false;
        $sslDaysLeft = null;
        $sslStatus = 'unknown';
        $sslIssuer = null;
        $sslExpiresAt = null;

        if (str_starts_with(strtolower($website->url), 'https://')) {
            try {
                $host = parse_url($website->url, PHP_URL_HOST);
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
                $sslStatus = 'invalid';
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
            'last_error' => $errorMessage,
            'ip_address' => $ipAddress ?? $website->ip_address,
            'ssl_status' => $sslStatus,
            'ssl_issuer' => $sslIssuer ?? $website->ssl_issuer,
            'ssl_expires_at' => $sslExpiresAt ?? $website->ssl_expires_at,
            'last_checked_at' => now(),
            'last_status_change_at' => $statusChanged ? now() : $website->last_status_change_at,
        ]);

        if ($status === 'down') {
            $existingIncident = Incident::where('ci_id', $website->ci_id)
                ->where('source', 'monitoring')
                ->where('status', '!=', 'closed')
                ->first();

            if (! $existingIncident) {
                $attempts = 0;
                while ($attempts < 3) {
                    try {
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
                        break; // Success
                    } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                        $attempts++;
                        if ($attempts >= 3) throw $e;
                        usleep(100000); // 100ms delay
                    }
                }
            }
        }
    }
}
