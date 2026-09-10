@extends('layouts.app')

@section('title', 'Incident Management')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Monitoring & Insiden</span>
                <span>/</span>
                <span class="text-white">Insiden</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Incident Management</h1>
            <p class="text-xs text-slate-400 mt-0.5">Penanganan gangguan teknis, eskalasi sistem monitoring, dan analisis akar masalah (root cause)</p>
        </div>
        <div>
            <a href="{{ route('incidents.create') }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-lg shadow-rose-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Catat Insiden Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-4">
        <form action="{{ route('incidents.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <div>
                <select name="status" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200">
                    <option value="">Semua Status Insiden</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open (Baru)</option>
                    <option value="investigation" {{ request('status') == 'investigation' ? 'selected' : '' }}>Investigation (Investigasi)</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress (Penanganan)</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved (Selesai)</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed (Ditutup)</option>
                </select>
            </div>

            <div>
                <select name="priority" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200">
                    <option value="">Semua Prioritas</option>
                    <option value="critical" {{ request('priority') == 'critical' ? 'selected' : '' }}>Critical</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                </select>
            </div>

            <div>
                <select name="source" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200">
                    <option value="">Semua Sumber Deteksi</option>
                    <option value="service_desk" {{ request('source') == 'service_desk' ? 'selected' : '' }}>Service Desk</option>
                    <option value="monitoring" {{ request('source') == 'monitoring' ? 'selected' : '' }}>Automated Monitoring</option>
                    <option value="security_monitoring" {{ request('source') == 'security_monitoring' ? 'selected' : '' }}>CSIRT / Security</option>
                    <option value="manual_report" {{ request('source') == 'manual_report' ? 'selected' : '' }}>Manual Report</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-medium transition text-center">
                    Terapkan Filter
                </button>
                @if(request()->anyFilled(['status', 'priority', 'source']))
                    <a href="{{ route('incidents.index') }}" class="py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition text-center">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Nomor Insiden</th>
                        <th class="py-3 px-4">Judul Insiden</th>
                        <th class="py-3 px-4">Sumber Deteksi</th>
                        <th class="py-3 px-4">CI Terkait (CMDB)</th>
                        <th class="py-3 px-4">Prioritas</th>
                        <th class="py-3 px-4">Teknisi Bertugas</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($incidents as $inc)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-rose-400">
                                <a href="{{ route('incidents.show', $inc) }}" class="hover:underline">
                                    {{ $inc->incident_number }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4">
                                <p class="font-semibold text-white text-sm">{{ $inc->title }}</p>
                                <span class="text-[10px] text-slate-500">Terdeteksi: {{ $inc->detected_at->format('d M Y H:i') }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] bg-slate-800 text-slate-300 font-medium">
                                    {{ ucwords(str_replace('_', ' ', $inc->source)) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($inc->configurationItem)
                                    <a href="{{ route('cmdb.show', $inc->configurationItem) }}" class="font-mono text-blue-400 hover:underline block font-semibold">
                                        {{ $inc->configurationItem->ci_code }}
                                    </a>
                                    <span class="text-[10px] text-slate-400">{{ $inc->configurationItem->name }}</span>
                                @else
                                    <span class="text-slate-600">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-[10px] font-bold uppercase
                                    @if($inc->priority === 'critical') text-rose-400
                                    @elseif($inc->priority === 'high') text-orange-400
                                    @elseif($inc->priority === 'medium') text-yellow-400
                                    @else text-slate-400 @endif">
                                    {{ $inc->priority }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-slate-300">{{ $inc->assignedTechnician?->name ?? 'Belum Ditugaskan' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase
                                    @if($inc->status === 'open') bg-rose-500/20 text-rose-300 border border-rose-500/30
                                    @elseif($inc->status === 'investigation') bg-amber-500/20 text-amber-300 border border-amber-500/30
                                    @elseif($inc->status === 'in_progress') bg-indigo-500/20 text-indigo-300 border border-indigo-500/30
                                    @elseif($inc->status === 'resolved') bg-emerald-500/20 text-emerald-300 border border-emerald-500/30
                                    @else bg-slate-700 text-slate-300 @endif">
                                    {{ $inc->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('incidents.show', $inc) }}" class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-xs font-medium transition">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500">Tidak ada insiden aktif yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($incidents->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $incidents->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
