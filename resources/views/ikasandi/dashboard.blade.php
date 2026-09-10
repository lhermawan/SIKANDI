@extends('layouts.app')

@section('title', 'IKASANDI Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-indigo-950 via-slate-900 to-indigo-950 border border-indigo-800/40 p-6 rounded-2xl">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
                <span class="text-xs font-semibold text-cyan-400 uppercase tracking-wider">IKASANDI Kabupaten Ciamis</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Indeks Keamanan Informasi dan Persandian</h1>
            <p class="text-xs text-slate-400 mt-1">Evaluasi maturitas penerapan kebijakan, pengamanan aset TIK, dan tanggap darurat siber OPD</p>
        </div>
        <div>
            <a href="{{ route('ikasandi.assessment') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-lg shadow-indigo-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span>Buka Form Assessment OPD</span>
            </a>
        </div>
    </div>

    <!-- Status Blocks (matching Section 16 format) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- 1. Overall Score Widget -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-6 flex flex-col justify-between">
            <div>
                <span class="text-xs font-bold text-indigo-400 uppercase tracking-wider block mb-1">IKASANDI STATUS</span>
                <h3 class="text-lg font-bold text-white">Overall Compliance Score</h3>
                <p class="text-xs text-slate-400 mt-0.5">Rerata pemenuhan indikator persandian</p>
            </div>

            <div class="my-6">
                <div class="flex items-baseline justify-between mb-2">
                    <span class="text-4xl font-black text-white">{{ $avgScore }}%</span>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full
                        @if($avgScore >= 80) bg-emerald-500/20 text-emerald-300
                        @elseif($avgScore >= 60) bg-amber-500/20 text-amber-300
                        @else bg-rose-500/20 text-rose-300 @endif">
                        {{ $avgScore >= 80 ? 'Maturitas Baik' : ($avgScore >= 60 ? 'Cukup' : 'Perlu Peningkatan') }}
                    </span>
                </div>
                <div class="w-full bg-slate-800 rounded-full h-3 overflow-hidden">
                    <div class="bg-gradient-to-r from-cyan-500 via-blue-500 to-indigo-500 h-full rounded-full" style="width: {{ max(5, $avgScore) }}%"></div>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-800 text-[11px] text-slate-400 flex justify-between">
                <span>Rata-rata Risiko Aktif:</span>
                <span class="text-rose-400 font-bold">{{ $avgRisk }}%</span>
            </div>
        </div>

        <!-- 2. OPD Target vs Assessed -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-6 flex flex-col justify-between">
            <div>
                <span class="text-xs font-bold text-cyan-400 uppercase tracking-wider block mb-1">Target Evaluasi</span>
                <h3 class="text-lg font-bold text-white">Cakupan Penilaian OPD</h3>
                <p class="text-xs text-slate-400 mt-0.5">Dinas, Badan, Sekretariat, dan Kecamatan</p>
            </div>

            <div class="my-4 space-y-2.5 text-xs">
                <div class="flex justify-between items-center p-2 rounded-lg bg-slate-950/60 border border-slate-800">
                    <span class="text-slate-400">Total OPD Terdaftar</span>
                    <span class="text-base font-bold text-white">{{ $totalOpd }}</span>
                </div>
                <div class="flex justify-between items-center p-2 rounded-lg bg-slate-950/60 border border-slate-800">
                    <span class="text-slate-400">Telah Mengisi Assessment</span>
                    <span class="text-base font-bold text-emerald-400">{{ $assessedCount }}</span>
                </div>
                <div class="flex justify-between items-center p-2 rounded-lg bg-slate-950/60 border border-slate-800">
                    <span class="text-slate-400">Pending / Belum Mengisi</span>
                    <span class="text-base font-bold text-amber-400">{{ max(0, $totalOpd - $assessedCount) }}</span>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-800 text-[11px] text-slate-400 flex justify-between">
                <span>Persentase Partisipasi:</span>
                <span class="text-cyan-400 font-bold">{{ $totalOpd > 0 ? round(($assessedCount / $totalOpd) * 100) : 0 }}%</span>
            </div>
        </div>

        <!-- 3. Risk Level Summary -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-6 flex flex-col justify-between">
            <div>
                <span class="text-xs font-bold text-amber-400 uppercase tracking-wider block mb-1">Sebaran Risiko</span>
                <h3 class="text-lg font-bold text-white">Risk Heatmap Summary</h3>
                <p class="text-xs text-slate-400 mt-0.5">Potensi ancaman terhadap infrastruktur TIK</p>
            </div>

            <div class="grid grid-cols-2 gap-2 my-4 text-xs">
                <div class="p-2.5 rounded-xl bg-rose-950/30 border border-rose-800/40 text-center">
                    <span class="text-[10px] text-rose-400 uppercase font-semibold">Critical</span>
                    <p class="text-xl font-black text-rose-300">{{ $riskStats['critical'] }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-orange-950/30 border border-orange-800/40 text-center">
                    <span class="text-[10px] text-orange-400 uppercase font-semibold">High</span>
                    <p class="text-xl font-black text-orange-300">{{ $riskStats['high'] }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-amber-950/30 border border-amber-800/40 text-center">
                    <span class="text-[10px] text-amber-400 uppercase font-semibold">Medium</span>
                    <p class="text-xl font-black text-amber-300">{{ $riskStats['medium'] }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-emerald-950/30 border border-emerald-800/40 text-center">
                    <span class="text-[10px] text-emerald-400 uppercase font-semibold">Low</span>
                    <p class="text-xl font-black text-emerald-300">{{ $riskStats['low'] }}</p>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-800 text-[11px] text-slate-400 flex justify-between">
                <span>Insiden CSIRT Aktif:</span>
                <span class="text-rose-400 font-bold">{{ $openIncidents }} kasus</span>
            </div>
        </div>
    </div>

    <!-- OPD Assessment Results Table -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-slate-800 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-white text-base">Hasil Penilaian IKASANDI per OPD</h2>
                <p class="text-xs text-slate-400">Peringkat kepatuhan dan status verifikasi persandian</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Nama OPD / Lembaga</th>
                        <th class="py-3 px-4">Tahun Evaluasi</th>
                        <th class="py-3 px-4 text-center">Skor Kepatuhan</th>
                        <th class="py-3 px-4 text-center">Skor Risiko</th>
                        <th class="py-3 px-4">Status Pengajuan</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($assessments as $asm)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-4 font-semibold text-white">
                                {{ $asm->organization->name }}
                                <span class="block text-[10px] text-slate-500 font-normal">{{ $asm->organization->code }} &bull; {{ ucfirst($asm->organization->category) }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-300">
                                {{ $asm->year }} ({{ $asm->period }})
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="text-sm font-bold text-emerald-400">{{ $asm->compliance_score }}%</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="text-sm font-bold text-rose-400">{{ $asm->risk_score }}%</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-slate-800 text-slate-300">
                                    {{ $asm->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('ikasandi.assessment', ['org_id' => $asm->organization_id]) }}" class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-xs font-medium">
                                    Tinjau Kuesioner
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">Belum ada assessment yang diinputkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
