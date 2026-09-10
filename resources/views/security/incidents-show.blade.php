@extends('layouts.app')

@section('title', 'Detail Insiden Keamanan')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <a href="{{ route('security.incidents.index') }}" class="text-sm text-slate-400 hover:text-white transition flex items-center gap-2 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Insiden
        </a>
        <h1 class="text-2xl font-bold text-white tracking-tight">{{ $incident->title }}</h1>
        <p class="text-sm text-slate-400 mt-1">ID: {{ $incident->incident_code }} • EDR Rule: {{ $incident->detection_rule ?? 'Manual Report' }}</p>
    </div>
    
    <div class="flex items-center gap-3">
        @if($incident->severity === 'critical')
            <span class="px-3 py-1.5 rounded-lg bg-rose-500/20 text-rose-400 font-bold uppercase border border-rose-500/20 shadow-[0_0_15px_rgba(244,63,94,0.3)]">Critical</span>
        @elseif($incident->severity === 'high')
            <span class="px-3 py-1.5 rounded-lg bg-orange-500/20 text-orange-400 font-bold uppercase border border-orange-500/20 shadow-[0_0_15px_rgba(249,115,22,0.3)]">High</span>
        @endif
        
        <span class="px-3 py-1.5 rounded-lg bg-slate-800 text-white font-mono border border-slate-700 shadow-xl">
            Risk Score: <span class="{{ $incident->risk_score >= 80 ? 'text-rose-400' : ($incident->risk_score >= 50 ? 'text-orange-400' : 'text-emerald-400') }}">{{ $incident->risk_score }} / 100</span>
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
            <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-4 border-b border-slate-800 pb-2">Deskripsi & Konteks</h2>
            <div class="prose prose-invert prose-sm max-w-none text-slate-300">
                <p>{{ $incident->description }}</p>
            </div>
            
            @if($incident->source_ip || $incident->username || $incident->agent)
            <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 gap-4 border-t border-slate-800 pt-4">
                @if($incident->source_ip)
                <div>
                    <span class="block text-xs text-slate-500 mb-1">Source IP</span>
                    <span class="text-slate-200 font-mono text-sm bg-slate-950 px-2 py-1 rounded">{{ $incident->source_ip }}</span>
                </div>
                @endif
                @if($incident->username)
                <div>
                    <span class="block text-xs text-slate-500 mb-1">Target Username</span>
                    <span class="text-slate-200 font-mono text-sm bg-slate-950 px-2 py-1 rounded">{{ $incident->username }}</span>
                </div>
                @endif
                @if($incident->agent)
                <div>
                    <span class="block text-xs text-slate-500 mb-1">Affected Host</span>
                    <span class="text-slate-200 text-sm">{{ $incident->agent->hostname }}</span>
                </div>
                @endif
            </div>
            @endif
        </div>

        @if($incident->events->count() > 0)
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
            <div class="flex justify-between items-center mb-4 border-b border-slate-800 pb-2">
                <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Related Security Events</h2>
                <span class="text-xs bg-blue-500/20 text-blue-300 px-2 py-1 rounded-full">{{ $incident->events->count() }} events</span>
            </div>
            
            <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2">
                @foreach($incident->events->sortByDesc('timestamp') as $ev)
                <div class="relative pl-4 border-l-2 {{ $ev->action === 'success' || $ev->action === 'login_success' ? 'border-emerald-500/50' : 'border-slate-700' }}">
                    <div class="absolute w-2 h-2 rounded-full {{ $ev->action === 'success' || $ev->action === 'login_success' ? 'bg-emerald-500' : 'bg-slate-500' }} -left-[5px] top-1.5"></div>
                    <div class="text-[11px] text-slate-500 mb-0.5">{{ $ev->timestamp->format('H:i:s Y-m-d') }}</div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-slate-200">{{ strtoupper($ev->event_type) }} ({{ $ev->action }})</span>
                        @if($ev->username) <span class="text-xs text-slate-400 bg-slate-800 px-1.5 rounded">user: {{ $ev->username }}</span> @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    
    <div class="space-y-6">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
            <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-4 border-b border-slate-800 pb-2">Timeline & Status</h2>
            <div class="space-y-4 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">First Seen</span>
                    <span class="text-slate-300">{{ $incident->first_seen_at ? $incident->first_seen_at->format('Y-m-d H:i:s') : '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Last Seen</span>
                    <span class="text-slate-300">{{ $incident->last_seen_at ? $incident->last_seen_at->format('Y-m-d H:i:s') : '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">EDR Status</span>
                    <span class="text-emerald-400 font-bold">{{ $incident->edr_status }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">CSIRT Status</span>
                    <span class="text-blue-400 font-bold uppercase">{{ $incident->workflow_status }}</span>
                </div>
            </div>
        </div>
        
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
            <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-4 border-b border-slate-800 pb-2">Update Workflow</h2>
            <form action="{{ route('security.incidents.workflow', $incident) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Status Penanganan</label>
                    <select name="workflow_status" class="w-full bg-slate-950 border border-slate-800 text-white rounded-lg px-3 py-2 text-sm">
                        <option value="reported" {{ $incident->workflow_status == 'reported' ? 'selected' : '' }}>Reported</option>
                        <option value="triage" {{ $incident->workflow_status == 'triage' ? 'selected' : '' }}>Triage</option>
                        <option value="investigation" {{ $incident->workflow_status == 'investigation' ? 'selected' : '' }}>Investigation</option>
                        <option value="containment" {{ $incident->workflow_status == 'containment' ? 'selected' : '' }}>Containment</option>
                        <option value="eradication" {{ $incident->workflow_status == 'eradication' ? 'selected' : '' }}>Eradication</option>
                        <option value="recovery" {{ $incident->workflow_status == 'recovery' ? 'selected' : '' }}>Recovery</option>
                        <option value="closed" {{ $incident->workflow_status == 'closed' ? 'selected' : '' }}>Closed / False Positive</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 rounded-lg transition">Update Status</button>
            </form>
        </div>
    </div>
</div>
@endsection
