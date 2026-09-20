<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\AgentCommand;
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
            '/root/.cache'
        ]);

        $agent->commands()->create([
            'action' => 'scan_disk',
            'paths' => $paths,
            'status' => 'pending'
        ]);

        return redirect()->route('agents.disk.show', $agent)->with('success', 'Perintah pindai disk telah dikirim ke agen.');
    }

    public function requestDelete(Request $request, Agent $agent)
    {
        $request->validate([
            'paths' => 'required|array',
            'paths.*' => 'string'
        ]);

        $agent->commands()->create([
            'action' => 'delete_files',
            'paths' => $request->paths,
            'status' => 'pending'
        ]);

        return redirect()->route('agents.disk.show', $agent)->with('success', 'Perintah penghapusan telah dikirim ke agen.');
    }
}
