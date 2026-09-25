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
            <form action="{{ route('agents.approve', $agent) }}" method="POST" class="flex items-center gap-3">
                @csrf
                <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                    <input type="checkbox" name="create_ci" value="1" class="w-4 h-4 rounded bg-slate-950 border-slate-700 text-emerald-600 focus:ring-emerald-500">
                    <span>Buat CMDB Item</span>
                </label>
                <button class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm px-4 py-2 rounded-lg font-medium transition">
                    Approve Agent
                </button>
            </form>
        @endif

        @if($agent->status !== 'revoked')
            <a href="{{ route('agents.disk.show', $agent) }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg font-medium transition">
                Kelola Disk
            </a>
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
            <div class="p-5 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-white">Monitored Services</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Total: <span id="services-count">{{ $agent->services->count() }}</span> service</p>
                </div>
                @if($agent->services->count() > 0)
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                    <input
                        type="text"
                        id="service-search"
                        placeholder="Cari service..."
                        class="w-full sm:w-56 bg-slate-950 border border-slate-700 rounded-lg pl-9 pr-3 py-1.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500"
                        oninput="filterServices(this.value)"
                    >
                </div>
                @endif
            </div>
            <div class="p-4">
                @php
                    $sortedServices = $agent->services->sortBy('service_name')->values();
                @endphp
                @if($sortedServices->count() > 0)
                    <div class="max-h-72 overflow-y-auto pr-1 custom-scrollbar">
                        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-2" id="services-grid">
                            @foreach($sortedServices as $svc)
                                @php
                                    $isActive = in_array(strtolower($svc->status), ['running', 'active']);
                                @endphp
                                <div class="service-item flex items-center justify-between p-2 rounded-lg bg-slate-950/50 border border-slate-800/80 hover:bg-slate-800/50 transition" data-name="{{ strtolower($svc->service_name) }}">
                                    <span class="text-xs text-slate-300 truncate mr-2" title="{{ $svc->service_name }}">
                                        {{ $svc->service_name }}
                                    </span>
                                    <div class="flex items-center gap-1.5 shrink-0" title="Status: {{ $svc->status }}">
                                        <div class="w-1.5 h-1.5 rounded-full {{ $isActive ? 'bg-emerald-500 shadow-[0_0_4px_#10b981]' : 'bg-rose-500 shadow-[0_0_4px_#f43f5e]' }}"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div id="services-empty" class="hidden text-sm text-slate-500 text-center py-8">Tidak ada service yang cocok.</div>
                    </div>
                @else
                    <p class="text-sm text-slate-500 text-center py-6">Belum ada data service.</p>
                @endif
            </div>
        </div>

        <script>
        (function() {
            window.filterServices = function(query) {
                query = query.toLowerCase().trim();
                let visibleCount = 0;
                
                const items = document.querySelectorAll('.service-item');
                items.forEach(function(item) {
                    if ((item.dataset.name || '').includes(query)) {
                        item.style.display = 'flex';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });

                const emptyEl = document.getElementById('services-empty');
                if (emptyEl) {
                    emptyEl.style.display = visibleCount === 0 ? 'block' : 'none';
                }
                
                const countEl = document.getElementById('services-count');
                if (countEl) {
                    countEl.textContent = visibleCount;
                }
            };
        })();
        </script>

        <!-- Events -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
            <div class="p-5 border-b border-slate-800">
                <h2 class="text-sm font-semibold text-white">Event Log</h2>
            </div>
            <div class="p-5">
                @if($agent->securityEvents->count() > 0)
                    <div class="space-y-4 max-h-96 overflow-y-auto pr-2 custom-scrollbar">
                        @foreach($agent->securityEvents as $ev)
                            <div class="flex items-start gap-3 text-sm">
                                <div class="mt-0.5 shrink-0">
                                    @if($ev->severity === 'critical' || $ev->severity === 'high')
                                        <span class="w-2 h-2 rounded-full bg-rose-500 inline-block shadow-[0_0_5px_#f43f5e]"></span>
                                    @elseif($ev->severity === 'medium' || $ev->severity === 'warning')
                                        <span class="w-2 h-2 rounded-full bg-amber-500 inline-block shadow-[0_0_5px_#f59e0b]"></span>
                                    @else
                                        <span class="w-2 h-2 rounded-full bg-blue-500 inline-block shadow-[0_0_5px_#3b82f6]"></span>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between gap-4 mb-1">
                                        <span class="font-semibold text-slate-200 text-xs uppercase tracking-wider">{{ $ev->event_type }} ({{ $ev->action }})</span>
                                        <span class="text-[10px] text-slate-500 whitespace-nowrap">{{ $ev->timestamp->format('d M Y H:i:s') }}</span>
                                    </div>
                                    <p class="text-xs text-slate-400 leading-relaxed break-words whitespace-normal">{{ str_replace('**', '', $ev->narrative) }}</p>
                                    @if($ev->incident_id)
                                        <a href="{{ route('security.incidents.show', $ev->incident_id) }}" class="inline-block mt-1 text-[10px] text-blue-400 hover:underline">Lihat Insiden Terkait &rarr;</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500 text-center py-4">Belum ada log event keamanan dari Agent ini.</p>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
