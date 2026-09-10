@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gradient-to-r from-blue-900/40 via-indigo-900/30 to-slate-900 border border-blue-800/40 p-6 rounded-2xl">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-xs font-semibold text-emerald-400 uppercase tracking-wider">SIKANDI Single Gateway Platform</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Bidang Persandian dan Keamanan Informasi</h1>
            <p class="text-xs text-slate-400 mt-1">Diskominfo Pemerintah Kabupaten Ciamis — Monitoring & CMDB Hub Aktif</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('cmdb.graph') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-lg shadow-blue-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Buka CMDB Graph</span>
            </a>
            <a href="{{ route('cmdb.create') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold transition border border-slate-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah CI</span>
            </a>
        </div>
    </div>

    <!-- KPI Metrics Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- CI Card -->
        <a href="{{ route('cmdb.index') }}" class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 hover:border-blue-500/40 transition group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-slate-400">Configuration Items</span>
                <div class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center group-hover:scale-110 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                </div>
            </div>
            <div class="flex items-baseline justify-between">
                <span class="text-2xl font-bold text-white">{{ $stats['total_ci'] }}</span>
                <span class="text-[11px] text-blue-400 font-medium flex items-center gap-1">CMDB Core &rarr;</span>
            </div>
        </a>

        <!-- IT Asset Card -->
        <a href="{{ route('itam.index') }}" class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 hover:border-amber-500/40 transition group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-slate-400">Total IT Assets</span>
                <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center group-hover:scale-110 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                </div>
            </div>
            <div class="flex items-baseline justify-between">
                <span class="text-2xl font-bold text-white">{{ $stats['total_assets'] }}</span>
                <span class="text-[11px] text-amber-400 font-medium flex items-center gap-1">ITAM &rarr;</span>
            </div>
        </a>

        <!-- Websites Monitoring Card -->
        <a href="{{ route('monitoring.websites') }}" class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 hover:border-emerald-500/40 transition group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-slate-400">Website Uptime</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center group-hover:scale-110 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                </div>
            </div>
            <div class="flex items-baseline justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-bold text-emerald-400">{{ $stats['websites_up'] }} UP</span>
                    @if($stats['websites_down'] > 0)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-300 font-bold">{{ $stats['websites_down'] }} DOWN</span>
                    @endif
                </div>
                <span class="text-[11px] text-emerald-400 font-medium flex items-center gap-1">Monitor &rarr;</span>
            </div>
        </a>

        <!-- Tickets & Incidents -->
        <a href="{{ route('incidents.index') }}" class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800/80 hover:border-rose-500/40 transition group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-slate-400">Insiden Aktif</span>
                <div class="w-9 h-9 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center group-hover:scale-110 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <div class="flex items-baseline justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-bold text-rose-400">{{ $stats['active_incidents'] }}</span>
                    <span class="text-xs text-slate-500">({{ $stats['open_tickets'] }} Tiket)</span>
                </div>
                <span class="text-[11px] text-rose-400 font-medium flex items-center gap-1">Kelola &rarr;</span>
            </div>
        </a>
    </div>

    <!-- Main Content: CMDB & Recent Items -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Col 1 & 2: Recent CI & Active Tickets -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Configuration Items Table Card -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="font-bold text-white text-base">Configuration Items (CMDB Terkini)</h2>
                        <p class="text-xs text-slate-400">Komponen teknis terdaftar dalam basis data CMDB</p>
                    </div>
                    <a href="{{ route('cmdb.index') }}" class="text-xs text-blue-400 hover:text-blue-300 font-semibold">Lihat Semua ({{ $stats['total_ci'] }}) &rarr;</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-medium">
                                <th class="pb-2.5">Kode CI</th>
                                <th class="pb-2.5">Nama CI</th>
                                <th class="pb-2.5">Tipe</th>
                                <th class="pb-2.5">Status</th>
                                <th class="pb-2.5">Kritikalitas</th>
                                <th class="pb-2.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 text-slate-300">
                            @forelse($recentCis as $ci)
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="py-3 font-mono text-blue-400 font-semibold">
                                        <a href="{{ route('cmdb.show', $ci) }}" class="hover:underline">{{ $ci->ci_code }}</a>
                                    </td>
                                    <td class="py-3">
                                        <p class="font-medium text-white">{{ $ci->name }}</p>
                                        <p class="text-[10px] text-slate-500">{{ $ci->hostname ?? $ci->ip_address ?? $ci->url }}</p>
                                    </td>
                                    <td class="py-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-800 text-slate-300 border border-slate-700">
                                            {{ $ci->ciType->name }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold
                                            @if($ci->status === 'active') bg-emerald-500/20 text-emerald-300
                                            @elseif($ci->status === 'maintenance') bg-amber-500/20 text-amber-300
                                            @elseif($ci->status === 'down') bg-rose-500/20 text-rose-300
                                            @else bg-slate-500/20 text-slate-400 @endif">
                                            {{ strtoupper($ci->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <span class="text-[10px] font-bold uppercase
                                            @if($ci->criticality === 'critical') text-red-400
                                            @elseif($ci->criticality === 'high') text-orange-400
                                            @elseif($ci->criticality === 'medium') text-yellow-400
                                            @else text-slate-400 @endif">
                                            {{ $ci->criticality }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-right">
                                        <a href="{{ route('cmdb.show', $ci) }}" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-[11px] transition">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-slate-500">Belum ada data CI.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tickets & Incidents Table Card -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="font-bold text-white text-base">Tiket Layanan & Aduan Terkini</h2>
                        <p class="text-xs text-slate-400">Permohonan layanan dan aduan gangguan dari OPD</p>
                    </div>
                    <a href="{{ route('service-desk.tickets') }}" class="text-xs text-blue-400 hover:text-blue-300 font-semibold">Semua Tiket &rarr;</a>
                </div>

                <div class="space-y-3">
                    @forelse($recentTickets as $ticket)
                        <div class="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800/80 flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-mono text-xs font-semibold text-blue-400">{{ $ticket->ticket_number }}</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded bg-slate-800 text-slate-300">{{ $ticket->organization->name }}</span>
                                    <span class="text-[10px] font-bold uppercase
                                        @if($ticket->priority === 'critical') text-rose-400
                                        @elseif($ticket->priority === 'high') text-amber-400
                                        @else text-slate-400 @endif">
                                        {{ $ticket->priority }}
                                    </span>
                                </div>
                                <p class="text-sm font-semibold text-white truncate">{{ $ticket->title }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">Pemohon: {{ $ticket->requester->name }} &bull; {{ $ticket->created_at->diffForHumans() }}</p>
                            </div>
                            <div>
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                    @if($ticket->status === 'open') bg-blue-500/20 text-blue-300
                                    @elseif($ticket->status === 'in_progress') bg-amber-500/20 text-amber-300
                                    @elseif($ticket->status === 'resolved') bg-emerald-500/20 text-emerald-300
                                    @else bg-slate-700 text-slate-300 @endif">
                                    {{ strtoupper($ticket->status) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 text-center py-4">Belum ada tiket yang diajukan.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Col 3: Audit Trail Timeline & Quick Info -->
        <div class="space-y-6">
            <!-- Audit Trail Card -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="font-bold text-white text-base">Audit Trail Aktivitas</h2>
                        <p class="text-xs text-slate-400">Jejak rekaman perubahan platform</p>
                    </div>
                    <a href="{{ route('admin.audit-logs') }}" class="text-xs text-blue-400 hover:text-blue-300 font-semibold">Semua Log &rarr;</a>
                </div>

                <div class="relative pl-6 space-y-4 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-800">
                    @forelse($recentAuditLogs as $log)
                        <div class="relative text-xs">
                            <div class="absolute -left-6 top-1 w-2.5 h-2.5 rounded-full border-2 border-slate-900
                                @if($log->action === 'created') bg-emerald-400
                                @elseif($log->action === 'updated') bg-blue-400
                                @elseif($log->action === 'deleted') bg-rose-400
                                @else bg-slate-400 @endif">
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-500 mb-0.5">
                                <span class="font-semibold text-slate-300">{{ $log->user_name ?? 'System' }}</span>
                                <span>{{ $log->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-slate-200">
                                <span class="font-semibold uppercase text-[10px] px-1 py-0.2 rounded
                                    @if($log->action === 'created') bg-emerald-500/20 text-emerald-300
                                    @elseif($log->action === 'updated') bg-blue-500/20 text-blue-300
                                    @elseif($log->action === 'deleted') bg-rose-500/20 text-rose-300
                                    @else bg-slate-800 text-slate-400 @endif">
                                    {{ $log->action }}
                                </span>
                                {{ $log->module }}: <span class="text-white font-medium">{{ $log->record_name }}</span>
                            </p>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 py-4">Belum ada aktivitas tercatat.</p>
                    @endforelse
                </div>
            </div>

            <!-- Diskominfo Ciamis NOC Card -->
            <div class="bg-gradient-to-br from-slate-900 via-indigo-950/40 to-slate-900 border border-slate-800/80 rounded-2xl p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-sm">Pusat Sandi & CSIRT</h3>
                        <p class="text-[11px] text-slate-400">Pemerintah Kabupaten Ciamis</p>
                    </div>
                </div>
                <p class="text-xs text-slate-300 leading-relaxed mb-4">
                    Platform SIKANDI mengonsolidasikan seluruh Configuration Item (CI), aset fisik, pemantauan status website, tiket gangguan, hingga mitigasi insiden keamanan informasi secara terintegrasi 1 pintu.
                </p>
                <div class="pt-3 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
                    <span>Host Status</span>
                    <span class="text-emerald-400 font-semibold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        Operasional Normal
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
