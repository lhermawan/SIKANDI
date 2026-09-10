@extends('layouts.app')

@section('title', 'Security Log Detail')

@section('content')
<div class="mb-6">
    <a href="{{ route('security.logs.index') }}" class="text-sm text-slate-400 hover:text-white transition flex items-center gap-2 mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Security Logs
    </a>
    <h1 class="text-2xl font-bold text-white tracking-tight">Security Event Detail</h1>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
        <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-4 border-b border-slate-800 pb-2">Event Information</h2>
        
        <dl class="space-y-4 text-sm">
            <div>
                <dt class="text-slate-500 mb-1">Event ID</dt>
                <dd class="text-slate-300 font-mono text-xs">{{ $event->event_id }}</dd>
            </div>
            <div>
                <dt class="text-slate-500 mb-1">Timestamp</dt>
                <dd class="text-slate-300">{{ $event->timestamp->format('d M Y H:i:s T') }}</dd>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <dt class="text-slate-500 mb-1">Event Type</dt>
                    <dd class="text-white font-medium">{{ strtoupper($event->event_type) }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 mb-1">Action</dt>
                    <dd class="text-white font-medium">{{ strtoupper($event->action) }}</dd>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <dt class="text-slate-500 mb-1">Severity</dt>
                    <dd class="text-white font-medium uppercase">{{ $event->severity }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 mb-1">Risk Score</dt>
                    <dd class="text-white font-medium">{{ $event->risk_score }} / 100</dd>
                </div>
            </div>
            <div>
                <dt class="text-slate-500 mb-1">Reason</dt>
                <dd class="text-slate-300">{{ $event->reason ?? '-' }}</dd>
            </div>
        </dl>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
        <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-4 border-b border-slate-800 pb-2">Context Information</h2>
        
        <dl class="space-y-4 text-sm">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <dt class="text-slate-500 mb-1">Hostname</dt>
                    <dd class="text-slate-300">{{ $event->hostname }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 mb-1">Agent ID</dt>
                    <dd class="text-slate-300">{{ $event->agent->agent_id ?? '-' }}</dd>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <dt class="text-slate-500 mb-1">Username</dt>
                    <dd class="text-slate-300">{{ $event->username ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 mb-1">Source IP</dt>
                    <dd class="text-slate-300">{{ $event->source_ip ?? '-' }}</dd>
                </div>
            </div>
            <div>
                <dt class="text-slate-500 mb-1">Process</dt>
                <dd class="text-slate-300">{{ $event->process ?? '-' }}</dd>
            </div>
            
            @if($event->incident_id)
            <div class="mt-6 p-4 rounded-xl border border-rose-500/30 bg-rose-500/5">
                <p class="text-rose-400 font-semibold mb-2">Terhubung ke Insiden</p>
                <a href="{{ route('security.incidents.show', $event->incident_id) }}" class="text-blue-400 hover:underline">
                    Lihat Insiden: {{ $event->incident->title ?? 'INCIDENT' }}
                </a>
            </div>
            @endif
        </dl>
    </div>
</div>

@if($event->metadata)
<div class="mt-6 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
    <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-4 border-b border-slate-800 pb-2">Raw Metadata (JSON)</h2>
    <pre class="text-xs text-emerald-400 bg-slate-950 p-4 rounded-xl overflow-x-auto"><code>{{ json_encode($event->metadata, JSON_PRETTY_PRINT) }}</code></pre>
</div>
@endif

@endsection
