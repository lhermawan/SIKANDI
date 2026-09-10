@extends('layouts.app')

@section('title', 'Workspace Teknisi')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between bg-slate-900 border border-slate-800 p-6 rounded-2xl">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Ruang Kerja Teknisi NOC & Infrastruktur</h1>
            <p class="text-xs text-slate-400 mt-1">Daftar penugasan tiket perbaikan, eskalasi insiden, dan monitoring server aktif</p>
        </div>
        <div>
            <a href="{{ route('cmdb.graph') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Lihat Topologi CMDB</span>
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-xs text-slate-400">Tiket Ditugaskan</p>
            <p class="text-2xl font-bold text-blue-400 mt-1">{{ $stats['my_tickets'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-xs text-slate-400">Insiden Aktif</p>
            <p class="text-2xl font-bold text-rose-400 mt-1">{{ $stats['my_incidents'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-xs text-slate-400">CI Aktif Terpantau</p>
            <p class="text-2xl font-bold text-emerald-400 mt-1">{{ $stats['total_ci_active'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-xs text-slate-400">Website Status Down</p>
            <p class="text-2xl font-bold {{ $stats['websites_down'] > 0 ? 'text-rose-400' : 'text-slate-400' }} mt-1">{{ $stats['websites_down'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Assigned Tickets -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <h2 class="font-bold text-white text-sm mb-3">Tiket Ditugaskan Kepada Anda</h2>
            <div class="space-y-3">
                @forelse($assignedTickets as $ticket)
                    <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl flex items-center justify-between">
                        <div>
                            <span class="font-mono text-xs font-semibold text-blue-400">{{ $ticket->ticket_number }}</span>
                            <p class="text-sm font-medium text-white">{{ $ticket->title }}</p>
                            <p class="text-xs text-slate-400">{{ $ticket->organization->name }} &bull; {{ $ticket->created_at->diffForHumans() }}</p>
                        </div>
                        <a href="{{ route('service-desk.tickets') }}" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded text-xs">Respon</a>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-4">Tidak ada tiket tertunda.</p>
                @endforelse
            </div>
        </div>

        <!-- Active Incidents -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <h2 class="font-bold text-white text-sm mb-3">Insiden yang Ditangani</h2>
            <div class="space-y-3">
                @forelse($activeIncidents as $inc)
                    <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl flex items-center justify-between">
                        <div>
                            <span class="font-mono text-xs font-semibold text-rose-400">{{ $inc->incident_number }}</span>
                            <p class="text-sm font-medium text-white">{{ $inc->title }}</p>
                            <p class="text-xs text-slate-400">CI: {{ $inc->configurationItem?->name ?? 'N/A' }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-rose-500/20 text-rose-300 text-xs font-semibold uppercase">{{ $inc->status }}</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-4">Tidak ada insiden aktif.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
