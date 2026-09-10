@extends('layouts.app')

@section('title', 'Security Logs')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-white tracking-tight">Security Logs</h1>
        <p class="text-sm text-slate-400 mt-1">Raw telemetry & normalized security events dari agent.</p>
    </div>
</div>

{{-- Filter Bar --}}
<form method="GET" action="{{ route('security.logs.index') }}" class="mb-5">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col sm:flex-row gap-3 items-end">
        {{-- Agent Dropdown --}}
        <div class="flex-1 min-w-0">
            <label class="block text-xs text-slate-400 mb-1.5 font-medium">Filter Agent</label>
            <select name="agent_id" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
                <option value="">— Semua Agent —</option>
                @foreach($agents as $agent)
                    <option value="{{ $agent->id }}" {{ $agentId == $agent->id ? 'selected' : '' }}>
                        {{ $agent->hostname }} ({{ $agent->agent_id }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Search --}}
        <div class="flex-1 min-w-0">
            <label class="block text-xs text-slate-400 mb-1.5 font-medium">Cari</label>
            <div class="relative">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Event type, hostname, username, IP..."
                    class="w-full bg-slate-950 border border-slate-700 rounded-lg pl-9 pr-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500"
                >
            </div>
        </div>

        {{-- Buttons --}}
        <div class="flex gap-2 shrink-0">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg font-medium transition">
                Filter
            </button>
            @if($agentId || $search)
                <a href="{{ route('security.logs.index') }}" class="bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-sm px-4 py-2 rounded-lg font-medium transition">
                    Reset
                </a>
            @endif
        </div>
    </div>
</form>

{{-- Active Filter Badge --}}
@if($selectedAgent)
    <div class="mb-4 flex items-center gap-2">
        <span class="text-xs text-slate-400">Menampilkan log dari:</span>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-semibold">
            <span class="w-1.5 h-1.5 rounded-full bg-blue-400 inline-block"></span>
            {{ $selectedAgent->hostname }} ({{ $selectedAgent->agent_id }})
        </span>
    </div>
@endif

<div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead>
                <tr class="bg-slate-950 border-b border-slate-800 text-slate-400 text-xs font-semibold uppercase tracking-wider">
                    <th class="px-6 py-4">Timestamp</th>
                    <th class="px-6 py-4">Severity</th>
                    <th class="px-6 py-4">Event Type</th>
                    <th class="px-6 py-4">Host/Agent</th>
                    <th class="px-6 py-4">User/IP</th>
                    <th class="px-6 py-4">Risk Score</th>
                    <th class="px-6 py-4 text-right">Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @forelse($events as $event)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-6 py-4 text-slate-300">
                            {{ $event->timestamp->format('d M Y H:i:s') }}
                        </td>
                        <td class="px-6 py-4">
                            @if($event->severity === 'critical')
                                <span class="px-2 py-1 rounded bg-rose-500/20 text-rose-400 text-[10px] font-bold uppercase border border-rose-500/20">Critical</span>
                            @elseif($event->severity === 'high')
                                <span class="px-2 py-1 rounded bg-orange-500/20 text-orange-400 text-[10px] font-bold uppercase border border-orange-500/20">High</span>
                            @elseif($event->severity === 'medium')
                                <span class="px-2 py-1 rounded bg-amber-500/20 text-amber-400 text-[10px] font-bold uppercase border border-amber-500/20">Medium</span>
                            @elseif($event->severity === 'low')
                                <span class="px-2 py-1 rounded bg-blue-500/20 text-blue-400 text-[10px] font-bold uppercase border border-blue-500/20">Low</span>
                            @else
                                <span class="px-2 py-1 rounded bg-slate-500/20 text-slate-400 text-[10px] font-bold uppercase border border-slate-500/20">Info</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-white font-medium block">{{ strtoupper($event->event_type) }}</span>
                            <span class="text-xs text-slate-500">{{ strtoupper($event->action) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-slate-300 block">{{ $event->hostname }}</span>
                            <span class="text-xs text-slate-500">{{ $event->agent->agent_id ?? 'Unknown Agent' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-slate-300 block">{{ $event->username ?? '-' }}</span>
                            <span class="text-xs text-slate-500">{{ $event->source_ip ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-mono text-xs {{ $event->risk_score >= 80 ? 'text-rose-400' : ($event->risk_score >= 50 ? 'text-amber-400' : 'text-slate-400') }}">
                                {{ $event->risk_score }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('security.logs.show', $event) }}" class="text-blue-400 hover:text-blue-300 font-medium text-xs">Lihat</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-slate-500">Belum ada security log yang terekam.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($events->hasPages())
        <div class="px-6 py-4 border-t border-slate-800">{{ $events->links() }}</div>
    @endif
</div>
@endsection

