<?php

namespace App\Services;

use App\Models\IpReputation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ThreatIntelService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.abuseipdb.com/api/v2';

    public function __construct()
    {
        $this->apiKey = config('services.abuseipdb.key', '');
    }

    /**
     * Mengecek reputasi IP. Menggunakan data cache jika masih dalam batas 24 jam.
     */
    public function checkIp(string $ip, bool $force = false): ?IpReputation
    {
        if (empty($this->apiKey)) {
            Log::warning("AbuseIPDB API Key is missing. Skipping check for IP: {$ip}");
            return null;
        }

        // Jangan mengecek IP lokal / private
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return null;
        }

        $reputation = IpReputation::firstWhere('ip_address', $ip);

        // Jika tidak di-force dan kita baru mengeceknya dalam 24 jam terakhir, gunakan cache
        if (!$force && $reputation && $reputation->last_checked_at && $reputation->last_checked_at->diffInHours(now()) < 24) {
            return $reputation;
        }

        try {
            $response = Http::withHeaders([
                'Key' => $this->apiKey,
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/check", [
                'ipAddress' => $ip,
                'maxAgeInDays' => 90
            ]);

            if ($response->successful()) {
                $data = $response->json('data');

                if (!$reputation) {
                    $reputation = new IpReputation(['ip_address' => $ip]);
                }

                $reputation->fill([
                    'is_public' => $data['isPublic'] ?? true,
                    'abuse_confidence_score' => $data['abuseConfidenceScore'] ?? 0,
                    'country_code' => $data['countryCode'] ?? null,
                    'usage_type' => $data['usageType'] ?? null,
                    'isp' => $data['isp'] ?? null,
                    'domain' => $data['domain'] ?? null,
                    'total_reports' => $data['totalReports'] ?? 0,
                    'raw_data' => $data,
                    'last_checked_at' => now(),
                ]);

                $reputation->save();
                return $reputation;
            } else {
                Log::error("AbuseIPDB API Error for IP {$ip}: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("ThreatIntelService Exception for IP {$ip}: " . $e->getMessage());
        }

        return $reputation;
    }
}
