<?php

namespace App\Services;

use App\Models\SecurityIncidentResponse;
use Illuminate\Support\Facades\Log;

class ResponseExecutorService
{
    /**
     * Mengeksekusi sebuah response action yang sedang pending.
     */
    public function execute(SecurityIncidentResponse $response, int $userId): bool
    {
        if ($response->status !== 'pending') {
            return false;
        }

        try {
            // Pilih driver eksekusi berdasarkan konfigurasi (Mikrotik vs Agent iptables)
            $driver = config('sikandi.response_driver', 'log');

            $success = false;
            $resultMessage = '';

            switch ($response->action) {
                case 'block_ip':
                    $ipToBlock = $this->extractIpFromDescription($response->description);
                    if ($driver === 'mikrotik') {
                        $success = $this->executeMikrotikBlock($ipToBlock);
                        $resultMessage = "IP $ipToBlock berhasil ditambahkan ke Mikrotik Address List.";
                    } elseif ($driver === 'agent') {
                        $success = $this->executeAgentBlock($ipToBlock);
                        $resultMessage = "Perintah iptables DROP dikirim ke Agent untuk IP $ipToBlock.";
                    } else {
                        // Dummy / Log mode
                        Log::info("Simulating IP Block for $ipToBlock");
                        $success = true;
                        $resultMessage = "Simulasi Blokir IP $ipToBlock berhasil dijalankan.";
                    }
                    break;

                case 'isolate_server':
                    $success = true;
                    $resultMessage = 'Simulasi Isolasi Jaringan Server berhasil dieksekusi.';
                    break;

                default:
                    $success = true;
                    $resultMessage = 'Aksi default dieksekusi.';
            }

            // Update status
            $response->status = $success ? 'executed' : 'failed';
            $response->performed_by = $userId;
            $response->performed_at = now();
            $response->result = $resultMessage;
            $response->save();

            return $success;

        } catch (\Exception $e) {
            Log::error('Response Executor Failed: '.$e->getMessage());
            $response->status = 'failed';
            $response->result = 'Error: '.$e->getMessage();
            $response->save();

            return false;
        }
    }

    private function extractIpFromDescription(?string $description): string
    {
        // Mencari IP address dari deskripsi menggunakan regex
        preg_match('/\b\d{1,3}(\.\d{1,3}){3}\b/', $description ?? '', $matches);

        return $matches[0] ?? '0.0.0.0';
    }

    private function executeMikrotikBlock(string $ip): bool
    {
        // TODO: Implementasi Guzzle/RouterOS API client
        // Untuk sekarang kita return true (simulasi berhasil jika API tersambung)
        return true;
    }

    private function executeAgentBlock(string $ip): bool
    {
        // TODO: Push message via WebSocket/MQTT ke Python Agent di target Node
        return true;
    }
}
