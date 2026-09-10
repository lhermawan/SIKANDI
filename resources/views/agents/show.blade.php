@extends('layouts.app')

@section('title', 'Detail Agent: ' . $agent->hostname)

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('agents.index') }}" class="p-2 bg-slate-900 border border-slate-800 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">{{ $agent->hostname }}</h1>
            <p class="text-sm text-slate-400 mt-1">Agent ID: {{ $agent->agent_id }}</p>
        </div>
    </div>
    
    <div class="flex items-center gap-2">
        @if($agent->status === 'pending')
            <form action="{{ route('agents.approve', $agent) }}" method="POST">
                @csrf
                <button class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm px-4 py-2 rounded-lg font-medium transition">
                    Approve Agent
                </button>
            </form>
        @endif

        @if($agent->status !== 'revoked')
            <form action="{{ route('agents.revoke', $agent) }}" method="POST" onsubmit="return confirm('Revoke akses agent ini? Agent tidak akan bisa mengirim data lagi.');">
                @csrf
                <button class="bg-rose-950 text-rose-500 hover:bg-rose-900 hover:text-rose-400 border border-rose-900 text-sm px-4 py-2 rounded-lg font-medium transition">
                    Revoke Access
                </button>
            </form>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="space-y-6 lg:col-span-1">
        <!-- Info Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
            <div class="p-5 border-b border-slate-800 flex justify-between items-center">
                <h2 class="text-sm font-semibold text-white">Informasi Agent</h2>
                @if($agent->status === 'online')
                    <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 text-[10px] font-bold uppercase tracking-wider border border-emerald-500/20">Online</span>
                @else
                    <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-400 text-[10px] font-bold uppercase tracking-wider border border-slate-700">{{ $agent->status }}</span>
                @endif
            </div>
            <div class="p-5 space-y-4 text-sm">
                <div>
                    <p class="text-xs text-slate-500 mb-1">IP Address</p>
                    <p class="text-white">{{ $agent->ip_address ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 mb-1">Operating System</p>
                    <p class="text-white">{{ $agent->os }} {{ $agent->os_version }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 mb-1">Agent Version</p>
                    <p class="text-white">{{ $agent->agent_version }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 mb-1">Last Seen</p>
                    <p class="text-white">{{ $agent->last_seen_at ? $agent->last_seen_at->diffForHumans() : '-' }}</p>
                </div>
            </div>
        </div>

        <!-- CI Link Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
            <div class="p-5 border-b border-slate-800">
                <h2 class="text-sm font-semibold text-white">Tautkan ke CI Server</h2>
            </div>
            <div class="p-5">
                <form action="{{ route('agents.link', $agent) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <select name="ci_id" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
                            <option value="">-- Pilih Server CI --</option>
                            @foreach($cis as $ci)
                                <option value="{{ $ci->id }}" {{ $agent->ci_id == $ci->id ? 'selected' : '' }}>
                                    {{ $ci->ci_code }} - {{ $ci->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button class="w-full bg-slate-800 hover:bg-slate-700 text-white text-sm px-4 py-2 rounded-lg font-medium transition">
                        Update Link
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="space-y-6 lg:col-span-2">
        <!-- Metrics -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden p-5">
            <h2 class="text-sm font-semibold text-white mb-4">Metrik Terakhir</h2>
            @if($latestMetric)
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-slate-950 border border-slate-800 rounded-xl p-4">
                        <p class="text-xs text-slate-500 mb-1">CPU Usage</p>
                        <p class="text-2xl font-bold {{ $latestMetric->cpu_usage > 80 ? 'text-rose-400' : 'text-emerald-400' }}">
                            {{ number_format($latestMetric->cpu_usage, 1) }}%
                        </p>
                    </div>
                    <div class="bg-slate-950 border border-slate-800 rounded-xl p-4">
                        <p class="text-xs text-slate-500 mb-1">Memory Usage</p>
                        <p class="text-2xl font-bold {{ $latestMetric->memory_usage > 80 ? 'text-rose-400' : 'text-blue-400' }}">
                            {{ number_format($latestMetric->memory_usage, 1) }}%
                        </p>
                    </div>
                    <div class="bg-slate-950 border border-slate-800 rounded-xl p-4">
                        <p class="text-xs text-slate-500 mb-1">Disk Usage</p>
                        <p class="text-2xl font-bold {{ $latestMetric->disk_usage > 80 ? 'text-rose-400' : 'text-amber-400' }}">
                            {{ number_format($latestMetric->disk_usage, 1) }}%
                        </p>
                    </div>
                </div>
            @else
                <p class="text-sm text-slate-500 text-center py-4">Belum ada metrik yang diterima.</p>
            @endif
        </div>

        <!-- Services -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
            <div class="p-5 border-b border-slate-800">
                <h2 class="text-sm font-semibold text-white">Monitored Services</h2>
            </div>
            <div class="p-0">
                @php
                    $sortedServices = $agent->services->sortBy(function($svc) {
                        return strtolower($svc->status) === 'running' ? 0 : 1;
                    })->values();
                @endphp
                @if($sortedServices->count() > 0)
                    <div x-data="{ expanded: false }">
                        <table class="w-full text-left text-sm">
                            <tbody class="divide-y divide-slate-800">
                                @foreach($sortedServices as $index => $svc)
                                    <tr class="hover:bg-slate-800/30 transition-all" x-show="expanded || {{ $index }} < 5">
                                        <td class="px-5 py-3 font-medium text-slate-300">{{ $svc->service_name }}</td>
                                        <td class="px-5 py-3 text-right">
                                            @if(strtolower($svc->status) === 'running')
                                                <span class="text-emerald-400 text-xs font-semibold">● Running</span>
                                            @else
                                                <span class="text-rose-400 text-xs font-semibold">● {{ $svc->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($sortedServices->count() > 5)
                            <button @click="expanded = !expanded" class="w-full py-3 text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800/40 border-t border-slate-800 transition">
                                <span x-text="expanded ? 'Tampilkan Lebih Sedikit' : 'Lihat Semua ({{ $sortedServices->count() }})'"></span>
                            </button>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-slate-500 text-center py-6">Belum ada data service.</p>
                @endif
            </div>
        </div>

        <!-- Events -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
            <div class="p-5 border-b border-slate-800">
                <h2 class="text-sm font-semibold text-white">Event Log</h2>
            </div>
            <div class="p-5">
                @if($agent->events->count() > 0)
                    <div class="space-y-4">
                        @foreach($agent->events as $ev)
                            <div class="flex items-start gap-3 text-sm">
                                <div class="mt-0.5">
                                    @if($ev->severity === 'critical' || $ev->severity === 'high')
                                        <span class="w-2 h-2 rounded-full bg-rose-500 inline-block"></span>
                                    @elseif($ev->severity === 'warning')
                                        <span class="w-2 h-2 rounded-full bg-amber-500 inline-block"></span>
                                    @else
                                        <span class="w-2 h-2 rounded-full bg-blue-500 inline-block"></span>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-white">{{ $ev->message ?? $ev->type }}</p>
                                    <p class="text-xs text-slate-500">{{ $ev->created_at->format('d M Y H:i:s') }}</p>
                                    @if($ev->incident_id)
                                        <a href="#" class="text-[10px] text-blue-400 hover:underline">Linked to Incident</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500 text-center py-4">Belum ada log event.</p>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
