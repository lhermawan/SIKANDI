<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\Request;

class DiskManagerController extends Controller
{
    public function show(Agent $agent)
    {
        $agent->load('metrics');
        $latestMetric = $agent->metrics()->latest()->first();

        // Ambil command scan_disk terakhir
        $latestScan = $agent->commands()
            ->where('action', 'scan_disk')
            ->latest()
            ->first();

        return view('agents.disk', compact('agent', 'latestMetric', 'latestScan'));
    }

    public function requestScan(Request $request, Agent $agent)
    {
        $paths = $request->input('paths', [
            '/var/cache',
            '/tmp',
            '/var/www',
            '/root/.npm',
            '/root/.cache',
        ]);

        $agent->commands()->create([
            'action' => 'scan_disk',
            'paths' => $paths,
            'status' => 'pending',
        ]);

        return redirect()->route('agents.disk.show', $agent)->with('success', 'Perintah pindai disk telah dikirim ke agen.');
    }

    public function requestDelete(Request $request, Agent $agent)
    {
        $request->validate([
            'paths' => 'required|array',
            'paths.*' => 'string',
        ]);

        // Daftar direktori yang diizinkan (allowlist)
        $allowedPrefixes = [
            '/var/cache/',
            '/tmp/',
            '/root/.npm/',
            '/root/.cache/',
        ];

        // Validasi setiap path untuk memastikan berada di dalam direktori yang diizinkan
        $validPaths = [];
        foreach ($request->paths as $path) {
            // Cegah directory traversal (contoh: /var/cache/../../etc/passwd)
            if (str_contains($path, '..')) {
                return redirect()->route('agents.disk.show', $agent)
                    ->with('error', "Path mengandung pola directory traversal yang dilarang: {$path}");
            }

            $isAllowed = false;
            foreach ($allowedPrefixes as $prefix) {
                if (str_starts_with($path, $prefix)) {
                    $isAllowed = true;
                    break;
                }
            }

            if (! $isAllowed) {
                return redirect()->route('agents.disk.show', $agent)
                    ->with('error', "Path tidak diizinkan untuk dihapus: {$path}");
            }
            $validPaths[] = $path;
        }

        $agent->commands()->create([
            'action' => 'delete_files',
            'paths' => $validPaths,
            'status' => 'pending',
        ]);

        return redirect()->route('agents.disk.show', $agent)->with('success', 'Perintah penghapusan telah dikirim ke agen.');
    }
}
