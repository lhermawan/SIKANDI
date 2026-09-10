@extends('layouts.app')

@section('title', 'Executive Management Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Executive Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 border border-indigo-900/40 p-6 rounded-2xl">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 uppercase tracking-wider">Executive Overview</span>
                <h1 class="text-2xl font-bold text-white tracking-tight mt-2">Ringkasan Eksekutif Keamanan & Persandian</h1>
                <p class="text-xs text-slate-400 mt-1">Dashboard pimpinan untuk memantau postur keamanan informasi, aset TIK, dan indeks IKASANDI Pemkab Ciamis</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('ikasandi.dashboard') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-lg shadow-indigo-600/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Laporan IKASANDI Lengkap</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 6 Executive Numbers (matching prompt Section 23) -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 text-center">
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">IT Asset</p>
            <p class="text-3xl font-black text-white mt-1">{{ number_format($stats['total_assets']) }}</p>
            <span class="text-[10px] text-slate-500">Perangkat Fisik</span>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 text-center">
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Config Items</p>
            <p class="text-3xl font-black text-cyan-400 mt-1">{{ number_format($stats['total_ci']) }}</p>
            <span class="text-[10px] text-slate-500">Komponen CMDB</span>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 text-center">
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Website OPD</p>
            <p class="text-3xl font-black text-emerald-400 mt-1">{{ number_format($stats['total_websites']) }}</p>
            <span class="text-[10px] text-slate-500">Aktif Dipantau</span>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 text-center">
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Insiden Terbuka</p>
            <p class="text-3xl font-black text-rose-400 mt-1">{{ number_format($stats['open_incidents']) }}</p>
            <span class="text-[10px] text-slate-500">Perlu Penanganan</span>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 text-center">
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Risiko Kritis</p>
            <p class="text-3xl font-black text-amber-400 mt-1">{{ number_format($stats['high_risks']) }}</p>
            <span class="text-[10px] text-slate-500">Level High & Critical</span>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 text-center">
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Indeks IKASANDI</p>
            <p class="text-3xl font-black text-indigo-400 mt-1">{{ $stats['ikasandi_avg_score'] }}%</p>
            <span class="text-[10px] text-indigo-300">Rerata Kepatuhan</span>
        </div>
    </div>

    <!-- IKASANDI Overview & Critical Risks -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- IKASANDI Readiness Status -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6">
            <h2 class="text-base font-bold text-white mb-1">Status Maturitas IKASANDI Seluruh OPD</h2>
            <p class="text-xs text-slate-400 mb-4">Progres penilaian kepatuhan keamanan informasi di lingkungan Pemkab Ciamis</p>

            <div class="mb-4">
                <div class="flex justify-between text-xs font-semibold mb-1">
                    <span class="text-slate-300">Skor Keseluruhan (Overall Compliance)</span>
                    <span class="text-indigo-400">{{ $stats['ikasandi_avg_score'] }}%</span>
                </div>
                <div class="w-full bg-slate-800 rounded-full h-3.5 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-500 h-full rounded-full transition-all" style="width: {{ max(5, $stats['ikasandi_avg_score']) }}%"></div>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 pt-3 border-t border-slate-800 text-center text-xs">
                <div>
                    <span class="text-slate-500 block">Total OPD Target</span>
                    <span class="text-lg font-bold text-white">{{ $stats['total_opd'] }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Telah Di-Assessed</span>
                    <span class="text-lg font-bold text-emerald-400">{{ $stats['assessed_opd'] }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Belum Evaluasi</span>
                    <span class="text-lg font-bold text-amber-400">{{ max(0, $stats['total_opd'] - $stats['assessed_opd']) }}</span>
                </div>
            </div>
        </div>

        <!-- Critical Risks -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-white">Register Risiko Prioritas Tinggi</h2>
                    <p class="text-xs text-slate-400">Ancaman dengan dampak operasional signifikan</p>
                </div>
                <a href="{{ route('security.risks') }}" class="text-xs text-blue-400 hover:underline">Semua Risiko</a>
            </div>

            <div class="space-y-3">
                @forelse($criticalRisks as $risk)
                    <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-mono text-xs font-semibold text-rose-400">{{ $risk->risk_code }}</span>
                                <span class="text-[10px] px-2 py-0.5 rounded bg-rose-500/20 text-rose-300 font-bold uppercase">{{ $risk->risk_level }}</span>
                            </div>
                            <p class="text-sm font-semibold text-white">{{ $risk->title }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">CI Terkait: {{ $risk->configurationItem?->name ?? 'Semua CI' }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-black text-rose-400">{{ $risk->risk_score }}</span>
                            <span class="block text-[9px] text-slate-500">Skor / 25</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-4">Tidak ada risiko level tinggi yang terdaftar.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
