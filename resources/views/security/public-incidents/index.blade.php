@extends('layouts.app')

@section('title', 'Meja Triage Insiden Publik (WhatsApp / CSIRT)')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Security & CSIRT</span>
                <span>/</span>
                <span class="text-white">Insiden Publik</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-3">
                <span>Meja Triage Insiden Publik</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    WhatsApp Bot Connected
                </span>
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Antrean pelaporan dugaan insiden siber masuk dari WhatsApp (bot-sandikami) & portal publik sebelum divalidasi ke Security Incidents resmi.</p>
        </div>
    </div>

    {{-- Stats Cards / Filter Tabs --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('security.public-incidents.index', ['status' => 'all']) }}" 
           class="p-4 rounded-2xl border transition {{ $status === 'all' ? 'bg-slate-800 border-blue-500/50 shadow-lg shadow-blue-500/10' : 'bg-slate-900 border-slate-800 hover:border-slate-700' }}">
            <p class="text-xs font-medium text-slate-400">Total Laporan Masuk</p>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-bold text-white">{{ $counts['all'] }}</span>
                <span class="text-xs text-slate-400">Semua Tiket</span>
            </div>
        </a>

        <a href="{{ route('security.public-incidents.index', ['status' => 'pending_review']) }}" 
           class="p-4 rounded-2xl border transition {{ $status === 'pending_review' ? 'bg-slate-800 border-amber-500/50 shadow-lg shadow-amber-500/10' : 'bg-slate-900 border-slate-800 hover:border-slate-700' }}">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-amber-400">Menunggu Review</p>
                @if($counts['pending_review'] > 0)
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                @endif
            </div>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-bold text-amber-400">{{ $counts['pending_review'] }}</span>
                <span class="text-xs text-amber-400/70">Perlu Tindakan</span>
            </div>
        </a>

        <a href="{{ route('security.public-incidents.index', ['status' => 'verified']) }}" 
           class="p-4 rounded-2xl border transition {{ $status === 'verified' ? 'bg-slate-800 border-emerald-500/50 shadow-lg shadow-emerald-500/10' : 'bg-slate-900 border-slate-800 hover:border-slate-700' }}">
            <p class="text-xs font-medium text-emerald-400">Terverifikasi (Valid)</p>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-bold text-emerald-400">{{ $counts['verified'] }}</span>
                <span class="text-xs text-emerald-400/70">Masuk SOC</span>
            </div>
        </a>

        <a href="{{ route('security.public-incidents.index', ['status' => 'rejected']) }}" 
           class="p-4 rounded-2xl border transition {{ $status === 'rejected' ? 'bg-slate-800 border-rose-500/50 shadow-lg shadow-rose-500/10' : 'bg-slate-900 border-slate-800 hover:border-slate-700' }}">
            <p class="text-xs font-medium text-rose-400">Ditolak / Tidak Valid</p>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-bold text-rose-400">{{ $counts['rejected'] }}</span>
                <span class="text-xs text-rose-400/70">Spam / Closed</span>
            </div>
        </a>
    </div>

    {{-- Filter & Search Form --}}
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-xl">
        <form method="GET" action="{{ route('security.public-incidents.index') }}" class="flex-1 w-full flex flex-col sm:flex-row gap-3">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="relative flex-1">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor tiket, nama pelapor, WhatsApp, atau aset terdampak..." class="w-full bg-slate-950 border border-slate-700 rounded-xl pl-9 pr-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl text-sm font-semibold transition shrink-0">
                Cari
            </button>
            @if(request()->filled('search'))
                <a href="{{ route('security.public-incidents.index', ['status' => $status]) }}" class="bg-slate-800 hover:bg-slate-700 text-slate-300 px-4 py-2 rounded-xl text-sm font-semibold transition shrink-0">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Table List --}}
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-950/60 text-slate-400 text-xs uppercase font-semibold border-b border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5">Tiket / Waktu Masuk</th>
                        <th class="px-5 py-3.5">Pelapor (WhatsApp)</th>
                        <th class="px-5 py-3.5">Dugaan Insiden & Aset</th>
                        <th class="px-5 py-3.5">Bukti</th>
                        <th class="px-5 py-3.5">Status Triage</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($reports as $report)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="px-5 py-4">
                                <div class="font-mono font-bold text-blue-400">{{ $report->ticket_number }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1.5">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        {{ strtoupper($report->source) }}
                                    </span>
                                    <span>{{ $report->created_at->format('d M Y H:i') }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-white">{{ $report->reporter_name ?: 'Anonim' }}</div>
                                <div class="text-xs font-mono text-slate-400">{{ $report->whatsapp_from ?: ($report->reporter_contact ?: '-') }}</div>
                            </td>
                            <td class="px-5 py-4 max-w-xs">
                                <div class="inline-block px-2 py-0.5 rounded text-xs font-semibold bg-slate-800 text-slate-200 border border-slate-700 capitalize mb-1">
                                    {{ $report->incident_type ?: 'Belum terklasifikasi' }}
                                </div>
                                <div class="text-xs text-slate-400 truncate" title="{{ $report->affected_asset }}">
                                    🎯 {{ $report->affected_asset ?: 'Tidak disertakan' }}
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $attCount = is_array($report->attachments) ? count($report->attachments) : 0;
                                @endphp
                                @if($attCount > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        {{ $attCount }} file
                                    </span>
                                @else
                                    <span class="text-xs text-slate-500 italic">Tanpa file</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($report->status === 'pending_review')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                        Menunggu Review
                                    </span>
                                @elseif($report->status === 'verified')
                                    <div>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                            ✓ Valid (Eskalasi)
                                        </span>
                                        @if($report->securityIncident)
                                            <a href="{{ route('security.incidents.show', $report->security_incident_id) }}" class="block text-[11px] font-mono text-blue-400 hover:underline mt-1">
                                                🔗 {{ $report->securityIncident->incident_code }}
                                            </a>
                                        @endif
                                    </div>
                                @elseif($report->status === 'rejected')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                        ✕ Ditolak
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('security.public-incidents.show', $report->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 hover:border-slate-600 transition shadow-sm">
                                    <span>Tinjau Laporan</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="font-medium text-slate-400">Tidak ada laporan insiden publik pada kategori ini.</p>
                                <p class="text-xs mt-1">Laporan baru dari WhatsApp (bot-sandikami) akan otomatis muncul di sini secara real-time.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reports->hasPages())
            <div class="px-5 py-4 border-t border-slate-800">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
