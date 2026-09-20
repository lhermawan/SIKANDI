@extends('layouts.app')

@section('title', 'Threat Actors')

@section('content')
<div class="mb-6">
    <div class="flex flex-col md:flex-row justify-between md:items-end gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Daftar Threat Actors</h1>
            <p class="text-slate-400 text-sm mt-1">Daftar alamat IP dengan aktivitas mencurigakan yang terekam oleh agent.</p>
        </div>
        
        <!-- Filter Form -->
        <form action="{{ route('security.threat-actors.index') }}" method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari IP Address..." class="bg-slate-900 border border-slate-700 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2 placeholder-slate-500">
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                Filter
            </button>
            @if(request('search'))
                <a href="{{ route('security.threat-actors.index') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold rounded-lg transition">
                    Reset
                </a>
            @endif
        </form>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl text-sm font-medium">
        {{ session('success') }}
    </div>
@endif

<form action="{{ route('security.threat-actors.bulk-block') }}" method="POST" id="bulk-form">
    @csrf
    
    <div class="mb-4 flex items-center justify-between bg-slate-900/50 p-3 rounded-xl border border-slate-800">
        <div class="text-sm text-slate-400">
            <span id="selected-count" class="font-bold text-white">0</span> IP dipilih
        </div>
        <button type="submit" id="btn-bulk-block" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold uppercase rounded-lg transition shadow-lg shadow-rose-600/20 opacity-50 cursor-not-allowed disabled:opacity-50 disabled:cursor-not-allowed" disabled onclick="return confirm('Masukkan semua IP yang dipilih ke antrean pemblokiran (HitL)?');">
            ⚡ Draft Blokir (Bulk)
        </button>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-950/50 text-slate-400 font-medium text-xs uppercase tracking-wider">
                        <th class="px-4 py-4 border-b border-slate-800 w-10 text-center">
                            <input type="checkbox" id="check-all" class="rounded border-slate-700 bg-slate-800 text-rose-500 focus:ring-rose-500/50">
                        </th>
                        <th class="px-4 py-4 border-b border-slate-800">Source IP</th>
                        <th class="px-4 py-4 border-b border-slate-800">Target Agent</th>
                        <th class="px-4 py-4 border-b border-slate-800">Total Serangan</th>
                        <th class="px-4 py-4 border-b border-slate-800">Threat Intel (AbuseIPDB)</th>
                        <th class="px-4 py-4 border-b border-slate-800 text-right">Tindakan SOC</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 text-slate-300">
                    @forelse($topAttackers as $attacker)
                        <tr class="hover:bg-slate-800/40 transition {{ $attacker->block_status ? 'opacity-70' : '' }}">
                            <td class="px-4 py-4 text-center">
                                @if(!$attacker->block_status && (!isset($attacker->reputation) || !$attacker->reputation->is_whitelisted))
                                    <input type="checkbox" name="ips[]" value="{{ $attacker->source_ip }}" class="check-item rounded border-slate-700 bg-slate-800 text-rose-500 focus:ring-rose-500/50">
                                @endif
                            </td>
                            <td class="px-4 py-4 font-mono text-rose-400 font-bold">
                                <div class="flex items-center gap-2">
                                    @if($attacker->reputation && $attacker->reputation->country_code)
                                        <img src="https://flagcdn.com/16x12/{{ strtolower($attacker->reputation->country_code) }}.png" alt="{{ $attacker->reputation->country_code }}" class="w-4 h-3 rounded-sm opacity-80" title="{{ $attacker->reputation->country_code }}">
                                    @endif
                                    {{ $attacker->source_ip }}
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach(explode(', ', $attacker->targeted_agents) as $agent)
                                        <span class="px-2 py-0.5 bg-slate-800 text-slate-300 text-[10px] border border-slate-700 rounded">{{ $agent }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-4 font-semibold">{{ number_format($attacker->total_events) }} events</td>
                            <td class="px-4 py-4">
                                @if($attacker->reputation)
                                    @if($attacker->reputation->is_whitelisted)
                                        <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-blue-500/20 text-blue-400 border border-blue-500/30">
                                            Whitelisted (Dikecualikan)
                                        </span>
                                    @elseif($attacker->reputation->abuse_confidence_score >= 80)
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
                            <td class="px-4 py-4 text-right">
                                @if($attacker->reputation && $attacker->reputation->is_whitelisted)
                                    <span class="px-3 py-1.5 bg-blue-500/10 text-blue-400 border border-blue-500/30 rounded-lg shadow-sm text-[11px] font-bold uppercase inline-flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Whitelisted
                                    </span>
                                @elseif($attacker->block_status === 'executed')
                                    <span class="px-3 py-1.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 rounded-lg shadow-sm text-[11px] font-bold uppercase inline-flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Terblokir
                                    </span>
                                @elseif($attacker->block_status === 'pending')
                                    <span class="px-3 py-1.5 bg-amber-500/10 text-amber-400 border border-amber-500/30 rounded-lg shadow-sm text-[11px] font-bold uppercase inline-flex items-center gap-1.5 animate-pulse">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Menunggu
                                    </span>
                                @else
                                    <div class="flex gap-2 justify-end">
                                        <form action="{{ route('threat-actors.whitelist') }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="ip_address" value="{{ $attacker->source_ip }}">
                                            <button type="submit" class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-blue-400 font-medium rounded shadow shadow-slate-900/50 transition text-[11px] uppercase inline-flex items-center gap-1.5" onclick="return confirm('Kecualikan IP {{ $attacker->source_ip }} dari daftar peringatan (Whitelist)?');">
                                                Whitelist
                                            </button>
                                        </form>
                                        <button type="submit" form="single-block-{{ md5($attacker->source_ip) }}" class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-rose-400 font-medium rounded shadow shadow-slate-900/50 transition text-[11px] uppercase inline-flex items-center gap-1.5">
                                            Blokir
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
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
</form>

@foreach($topAttackers as $attacker)
    @if(!$attacker->block_status)
        <form id="single-block-{{ md5($attacker->source_ip) }}" action="{{ route('admin.soc.quick-block') }}" method="POST" onsubmit="return confirm('Masukkan IP ini ke antrean pemblokiran (HitL)?');" style="display: none;">
            @csrf
            <input type="hidden" name="ip" value="{{ $attacker->source_ip }}">
        </form>
    @endif
@endforeach

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkAll = document.getElementById('check-all');
    const checkboxes = document.querySelectorAll('.check-item');
    const btnBulk = document.getElementById('btn-bulk-block');
    const selectedCount = document.getElementById('selected-count');

    function updateBulkButton() {
        const checked = document.querySelectorAll('.check-item:checked').length;
        selectedCount.textContent = checked;
        if (checked > 0) {
            btnBulk.disabled = false;
            btnBulk.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            btnBulk.disabled = true;
            btnBulk.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            checkboxes.forEach(cb => {
                cb.checked = checkAll.checked;
            });
            updateBulkButton();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const allChecked = document.querySelectorAll('.check-item:checked').length === checkboxes.length;
            checkAll.checked = allChecked;
            updateBulkButton();
        });
    });
});
</script>
@endsection
