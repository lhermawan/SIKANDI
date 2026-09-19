@extends('layouts.app')

@section('title', 'Threat Actors')

@section('content')
<div class="mb-6">
    <div class="flex flex-col md:flex-row justify-between md:items-end gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Daftar Threat Actors</h1>
            <p class="text-slate-400 text-sm mt-1">Daftar alamat IP dengan aktivitas mencurigakan yang terekam oleh agent.</p>
        </div>
    </div>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="bg-slate-950/50 text-slate-400 font-medium text-xs uppercase tracking-wider">
                    <th class="px-6 py-4 border-b border-slate-800">Source IP</th>
                    <th class="px-6 py-4 border-b border-slate-800">Total Serangan</th>
                    <th class="px-6 py-4 border-b border-slate-800">Threat Intel (AbuseIPDB)</th>
                    <th class="px-6 py-4 border-b border-slate-800 text-right">Tindakan SOC</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800 text-slate-300">
                @forelse($topAttackers as $attacker)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-6 py-4 font-mono text-rose-400 font-bold">
                            <div class="flex items-center gap-2">
                                @if($attacker->reputation && $attacker->reputation->country_code)
                                    <img src="https://flagcdn.com/16x12/{{ strtolower($attacker->reputation->country_code) }}.png" alt="{{ $attacker->reputation->country_code }}" class="w-4 h-3 rounded-sm opacity-80" title="{{ $attacker->reputation->country_code }}">
                                @endif
                                {{ $attacker->source_ip }}
                            </div>
                        </td>
                        <td class="px-6 py-4 font-semibold">{{ number_format($attacker->total_events) }} events</td>
                        <td class="px-6 py-4">
                            @if($attacker->reputation)
                                @if($attacker->reputation->abuse_confidence_score >= 80)
                                    <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                        Skor: {{ $attacker->reputation->abuse_confidence_score }}% (Malicious)
                                    </span>
                                @elseif($attacker->reputation->abuse_confidence_score >= 20)
                                    <span class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                        Skor: {{ $attacker->reputation->abuse_confidence_score }}% (Suspicious)
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-md text-[11px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        Aman (Skor: 0%)
                                    </span>
                                @endif
                            @else
                                <span class="px-2.5 py-1 rounded-md text-[11px] bg-slate-800 text-slate-400 border border-slate-700 animate-pulse">
                                    Scanning...
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($attacker->block_status === 'executed')
                                <span class="px-3 py-1.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 rounded-lg shadow-sm text-[11px] font-bold uppercase inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Terblokir
                                </span>
                            @elseif($attacker->block_status === 'pending')
                                <span class="px-3 py-1.5 bg-amber-500/10 text-amber-400 border border-amber-500/30 rounded-lg shadow-sm text-[11px] font-bold uppercase inline-flex items-center gap-1.5 animate-pulse">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Menunggu Verifikasi
                                </span>
                            @else
                                <form action="{{ route('admin.soc.quick-block') }}" method="POST" class="inline-block" onsubmit="return confirm('Masukkan IP ini ke antrean pemblokiran (HitL)?');">
                                    @csrf
                                    <input type="hidden" name="ip" value="{{ $attacker->source_ip }}">
                                    <button type="submit" class="px-3 py-1.5 bg-rose-500 hover:bg-rose-600 text-white font-bold rounded-lg shadow-lg shadow-rose-500/20 transition text-[11px] uppercase inline-flex items-center gap-1.5">
                                        ⚡ Draft Blokir
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-slate-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p>Belum ada aktivitas serangan yang terekam.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($topAttackers->hasPages())
    <div class="p-4 border-t border-slate-800 bg-slate-950/50">
        {{ $topAttackers->links() }}
    </div>
    @endif
</div>
@endsection
