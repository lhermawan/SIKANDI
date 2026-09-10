@extends('layouts.app')

@section('title', 'Manajemen Risiko Keamanan Informasi')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Security</span>
                <span>/</span>
                <span class="text-white">Manajemen Risiko</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Risk Register & Matriks Heatmap 5x5</h1>
            <p class="text-xs text-slate-400 mt-0.5">Identifikasi ancaman, kerentanan, mitigasi risiko TIK, dan keterkaitan ke aset CMDB</p>
        </div>
        <div>
            <a href="{{ route('security.risks.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-lg shadow-blue-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Risiko Baru</span>
            </a>
        </div>
    </div>

    <!-- 5x5 Heatmap & Stats Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 5x5 Heatmap Matrix -->
        <div class="lg:col-span-2 bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5">
            <h2 class="font-bold text-white text-sm mb-1">Matriks Risiko 5x5 (Likelihood &times; Impact)</h2>
            <p class="text-xs text-slate-400 mb-4">Sebaran risiko berdasarkan probabilitas kejadian dan tingkat dampak kerusakan</p>

            <div class="flex items-center">
                <!-- Y-Axis Label -->
                <div class="w-8 -rotate-90 text-[10px] font-bold text-slate-400 uppercase text-center shrink-0">
                    Likelihood &rarr;
                </div>

                <!-- Matrix Grid -->
                <div class="flex-1">
                    <div class="grid grid-cols-5 gap-1.5 text-center text-xs font-mono">
                        @for($l = 5; $l >= 1; $l--)
                            @for($i = 1; $i <= 5; $i++)
                                @php
                                    $score = $l * $i;
                                    $count = $matrix[$l][$i] ?? 0;
                                    $bgColor = match(true) {
                                        $score >= 16 => 'bg-rose-900/50 border-rose-600 text-rose-200',
                                        $score >= 10 => 'bg-orange-900/50 border-orange-600 text-orange-200',
                                        $score >= 5 => 'bg-amber-900/40 border-amber-600 text-amber-200',
                                        default => 'bg-emerald-900/30 border-emerald-700 text-emerald-200',
                                    };
                                @endphp
                                <div class="p-3 rounded-xl border {{ $bgColor }} flex flex-col justify-center items-center h-14">
                                    <span class="text-base font-bold">{{ $count }}</span>
                                    <span class="text-[9px] opacity-75">L{{ $l }}xI{{ $i }} ({{ $score }})</span>
                                </div>
                            @endfor
                        @endfor
                    </div>
                    <!-- X-Axis Label -->
                    <div class="text-center text-[10px] font-bold text-slate-400 uppercase mt-2">
                        Dampak Kerusakan (Impact 1 - 5) &rarr;
                    </div>
                </div>
            </div>
        </div>

        <!-- Risk Levels Count -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 flex flex-col justify-between">
            <h2 class="font-bold text-white text-sm mb-3">Distribusi Tingkat Risiko</h2>
            <div class="space-y-3 text-xs">
                <div class="p-3 rounded-xl bg-rose-950/40 border border-rose-800/50 flex justify-between items-center">
                    <div>
                        <span class="font-bold text-rose-300 block">Critical Risk (16-25)</span>
                        <span class="text-[10px] text-slate-400">Mitigasi segera (Prioritas Utama)</span>
                    </div>
                    <span class="text-2xl font-black text-rose-400">{{ $stats['critical'] }}</span>
                </div>
                <div class="p-3 rounded-xl bg-orange-950/40 border border-orange-800/50 flex justify-between items-center">
                    <div>
                        <span class="font-bold text-orange-300 block">High Risk (10-15)</span>
                        <span class="text-[10px] text-slate-400">Tindakan pencegahan aktif</span>
                    </div>
                    <span class="text-2xl font-black text-orange-400">{{ $stats['high'] }}</span>
                </div>
                <div class="p-3 rounded-xl bg-amber-950/40 border border-amber-800/50 flex justify-between items-center">
                    <div>
                        <span class="font-bold text-amber-300 block">Medium Risk (5-9)</span>
                        <span class="text-[10px] text-slate-400">Monitoring berkala</span>
                    </div>
                    <span class="text-2xl font-black text-amber-400">{{ $stats['medium'] }}</span>
                </div>
                <div class="p-3 rounded-xl bg-emerald-950/30 border border-emerald-800/50 flex justify-between items-center">
                    <div>
                        <span class="font-bold text-emerald-300 block">Low Risk (1-4)</span>
                        <span class="text-[10px] text-slate-400">Risiko dapat diterima</span>
                    </div>
                    <span class="text-2xl font-black text-emerald-400">{{ $stats['low'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Risk Register Table -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <h2 class="font-bold text-white text-sm">Daftar Register Risiko Aktif</h2>
            <span class="text-xs text-slate-400">{{ $risks->total() }} Risiko Terdaftar</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Kode Risiko</th>
                        <th class="py-3 px-4">Judul & Ancaman</th>
                        <th class="py-3 px-4">Komponen Terkait (CI)</th>
                        <th class="py-3 px-4 text-center">Likelihood &times; Impact</th>
                        <th class="py-3 px-4">Skor & Level</th>
                        <th class="py-3 px-4">Penanggung Jawab</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($risks as $r)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-amber-400">
                                {{ $r->risk_code }}
                            </td>
                            <td class="py-3.5 px-4">
                                <p class="font-semibold text-white text-sm">{{ $r->title }}</p>
                                <span class="text-[10px] text-slate-400">Ancaman: {{ $r->threat ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($r->configurationItem)
                                    <a href="{{ route('cmdb.show', $r->configurationItem) }}" class="font-mono text-blue-400 hover:underline">
                                        {{ $r->configurationItem->ci_code }}
                                    </a>
                                @else
                                    <span class="text-slate-600">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold">
                                {{ $r->likelihood }} &times; {{ $r->impact }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    @if($r->risk_level === 'critical') bg-rose-500/20 text-rose-300
                                    @elseif($r->risk_level === 'high') bg-orange-500/20 text-orange-300
                                    @elseif($r->risk_level === 'medium') bg-amber-500/20 text-amber-300
                                    @else bg-emerald-500/20 text-emerald-300 @endif">
                                    {{ $r->risk_score }} &bull; {{ $r->risk_level }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-slate-300">{{ $r->owner?->name ?? 'Belum Ditugaskan' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] bg-slate-800 text-slate-300 uppercase font-semibold">
                                    {{ $r->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">Belum ada risiko terdaftar dalam register.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
