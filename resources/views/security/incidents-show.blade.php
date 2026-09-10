@extends('layouts.app')

@section('title', 'SOC Incident Console - ' . $incident->incident_code)

@section('content')
<!-- Header & Actions -->
<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <a href="{{ route('security.incidents.index') }}" class="text-sm text-slate-400 hover:text-white transition flex items-center gap-2 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Security Incidents
        </a>
        <div class="flex items-center gap-3">
            <h1 class="text-3xl font-bold text-white tracking-tight">{{ $incident->incident_code }}</h1>
            <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider
                @if($incident->severity === 'critical') bg-rose-500/20 text-rose-400 border border-rose-500/30
                @elseif($incident->severity === 'high') bg-orange-500/20 text-orange-400 border border-orange-500/30
                @elseif($incident->severity === 'medium') bg-yellow-500/20 text-yellow-400 border border-yellow-500/30
                @else bg-blue-500/20 text-blue-400 border border-blue-500/30 @endif">
                {{ $incident->severity }}
            </span>
            <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider bg-slate-800 text-slate-300 border border-slate-700">
                {{ str_replace('_', ' ', $incident->workflow_status) }}
            </span>
        </div>
        <h2 class="text-lg font-medium text-slate-300 mt-1">{{ str_replace('_', ' ', $incident->detection_rule) }}</h2>
        <p class="text-sm text-slate-500 mt-1 max-w-2xl line-clamp-2">{{ explode("\n", $incident->description)[0] }}</p>
    </div>
    
    <div class="flex flex-wrap items-center gap-2">
        @if($incident->workflow_status === 'reported')
            <form action="{{ route('security.incidents.workflow', $incident) }}" method="POST">
                @csrf
                <input type="hidden" name="workflow_status" value="investigating">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-sm font-semibold transition">Start Investigation</button>
            </form>
        @endif

        @if(in_array($incident->workflow_status, ['investigating', 'compromised']))
            <form action="{{ route('security.incidents.workflow', $incident) }}" method="POST">
                @csrf
                <input type="hidden" name="workflow_status" value="contained">
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm font-semibold transition">Mark Contained</button>
            </form>
            <button onclick="document.getElementById('modal-response').classList.remove('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 rounded-lg text-sm font-semibold transition">Add Response</button>
            <button onclick="document.getElementById('modal-evidence').classList.remove('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 rounded-lg text-sm font-semibold transition">Add Evidence</button>
        @endif

        @if(in_array($incident->workflow_status, ['contained', 'recovery']))
            <button onclick="document.getElementById('modal-resolve').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm font-semibold transition">Resolve Incident</button>
        @endif
        
        @if($incident->workflow_status === 'resolved')
             <form action="{{ route('security.incidents.workflow', $incident) }}" method="POST">
                @csrf
                <input type="hidden" name="workflow_status" value="investigating">
                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-sm font-semibold transition">Reopen</button>
            </form>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
    
    <!-- Left Column (Main Content) -->
    <div class="xl:col-span-2 space-y-6">
        
        <!-- Resolution Box (If Resolved) -->
        @if($incident->workflow_status === 'resolved')
        <div class="bg-emerald-950/30 border border-emerald-900/50 rounded-2xl p-6 shadow-xl relative overflow-hidden">
            <div class="absolute right-0 top-0 text-emerald-900/20">
                <svg class="w-32 h-32 -mr-8 -mt-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            </div>
            <h2 class="text-sm font-semibold text-emerald-400 uppercase tracking-wider mb-4">Resolution</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm relative z-10">
                <div>
                    <span class="block text-slate-500 mb-1">Resolution Type</span>
                    <span class="text-slate-200 font-medium">{{ str_replace('_', ' ', $incident->resolution_type) }}</span>
                </div>
                <div>
                    <span class="block text-slate-500 mb-1">Root Cause</span>
                    <span class="text-slate-200 font-medium">{{ $incident->root_cause }}</span>
                </div>
                <div class="sm:col-span-2">
                    <span class="block text-slate-500 mb-1">Final Assessment</span>
                    <p class="text-slate-300 bg-slate-900/50 p-3 rounded-lg border border-slate-800/50 mt-1">{!! nl2br(e($incident->resolution_summary)) !!}</p>
                </div>
                <div class="sm:col-span-2 text-xs text-emerald-500/70 mt-2">
                    Resolved at {{ $incident->resolved_at?->format('d M Y H:i') }}
                </div>
            </div>
        </div>
        @endif

        <!-- Incident Timeline -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
            <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-6">Incident Timeline</h2>
            
            <div class="relative border-l border-slate-700 ml-3 space-y-6">
                <!-- First Seen RAW -->
                <div class="relative pl-6">
                    <span class="absolute -left-[5px] top-1.5 w-2.5 h-2.5 rounded-full bg-slate-600 ring-4 ring-slate-900"></span>
                    <div class="text-xs text-slate-500 mb-1">{{ $incident->first_seen_at->format('10 M Y H:i:s') }}</div>
                    <div class="font-medium text-slate-300 uppercase">First Attack Activity</div>
                    @if($incident->source_ip)
                        <div class="text-sm text-slate-400 mt-1 font-mono">source: {{ $incident->source_ip }}</div>
                    @endif
                </div>
                
                <!-- The Detection -->
                <div class="relative pl-6">
                    <span class="absolute -left-[5.5px] top-1.5 w-3 h-3 rounded-full bg-rose-500 ring-4 ring-slate-900 shadow-[0_0_10px_rgba(244,63,94,0.6)]"></span>
                    <div class="text-xs text-rose-500/70 mb-1">{{ $incident->created_at->format('10 M Y H:i:s') }}</div>
                    <div class="font-bold text-rose-400 uppercase">{{ $incident->detection_rule }} DETECTED</div>
                    <div class="text-sm text-slate-300 mt-1 max-h-48 overflow-y-auto pr-2 prose prose-sm prose-invert">{!! nl2br(e($incident->description)) !!}</div>
                </div>

                <!-- Responses in Timeline -->
                @foreach($incident->responses as $resp)
                <div class="relative pl-6">
                    <span class="absolute -left-[5px] top-1.5 w-2.5 h-2.5 rounded-full bg-blue-500 ring-4 ring-slate-900"></span>
                    <div class="text-xs text-slate-500 mb-1">{{ $resp->performed_at->format('10 M Y H:i:s') }}</div>
                    <div class="font-medium text-blue-400 uppercase">RESPONSE: {{ $resp->action }}</div>
                    <div class="text-sm text-slate-400 mt-1">{{ $resp->description }}</div>
                    <div class="text-xs text-slate-500 mt-1">By: {{ $resp->performer->name ?? 'System' }}</div>
                </div>
                @endforeach

                <!-- Last Seen RAW -->
                <div class="relative pl-6">
                    <span class="absolute -left-[5px] top-1.5 w-2.5 h-2.5 rounded-full bg-slate-500 ring-4 ring-slate-900"></span>
                    <div class="text-xs text-slate-500 mb-1">{{ $incident->last_seen_at->format('10 M Y H:i:s') }}</div>
                    <div class="font-medium text-slate-400 uppercase">Last Attack Activity</div>
                </div>
            </div>
        </div>

        <!-- Investigation Checklist -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Investigation Checklist</h2>
            </div>
            
            <div class="space-y-6">
                @foreach($incident->tasks->groupBy('category') as $category => $tasks)
                <div>
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest border-b border-slate-800 pb-2 mb-3">{{ $category }}</h3>
                    <div class="space-y-2">
                        @foreach($tasks as $task)
                        <div class="flex items-start gap-3 p-2 rounded-lg hover:bg-slate-800/50 transition group">
                            <form action="{{ route('security.incidents.tasks.toggle', [$incident, $task]) }}" method="POST">
                                @csrf
                                <button type="submit" class="mt-0.5 w-5 h-5 rounded border flex items-center justify-center transition-colors {{ $task->status === 'COMPLETED' ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-600 text-transparent hover:border-slate-400' }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </form>
                            <div class="flex-1">
                                <p class="text-sm {{ $task->status === 'COMPLETED' ? 'text-slate-400 line-through' : 'text-slate-200' }}">{{ $task->task }}</p>
                                @if($task->status === 'COMPLETED')
                                    <p class="text-[10px] text-slate-500 mt-0.5">Checked by {{ $task->checker->name ?? 'User' }} at {{ $task->checked_at->format('H:i') }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Evidence -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-4">Evidence</h2>
                <div class="space-y-3">
                    @forelse($incident->evidence as $ev)
                    <div class="bg-slate-950 border border-slate-800 p-3 rounded-xl flex items-start gap-3">
                        <div class="p-2 bg-slate-900 rounded-lg text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-200">{{ $ev->title }}</p>
                            <p class="text-xs text-slate-500">{{ $ev->type }} • {{ $ev->collected_at->format('10 M H:i') }}</p>
                            @if($ev->description)
                                <p class="text-xs text-slate-400 mt-1">{{ $ev->description }}</p>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-slate-500 text-center py-4">No evidence added yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- Responses -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-4">Response Actions</h2>
                <div class="space-y-3">
                    @forelse($incident->responses as $resp)
                    <div class="bg-slate-950 border border-slate-800 p-3 rounded-xl">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-sm font-medium text-slate-200">{{ $resp->action }}</p>
                        </div>
                        <p class="text-xs text-slate-400 ml-6">{{ $resp->description }}</p>
                    </div>
                    @empty
                    <p class="text-sm text-slate-500 text-center py-4">No response actions taken yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column (Sidebar) -->
    <div class="space-y-6">
        
        <!-- Risk Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl text-center relative overflow-hidden">
            <div class="absolute inset-0 opacity-10" style="background: radial-gradient(circle at top right, {{ $incident->risk_score >= 80 ? '#f43f5e' : ($incident->risk_score >= 50 ? '#f97316' : '#10b981') }}, transparent 70%);"></div>
            <h2 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">Security Risk</h2>
            
            <div class="flex items-baseline justify-center gap-1 mb-2">
                <span class="text-6xl font-black tracking-tighter {{ $incident->risk_score >= 80 ? 'text-rose-500' : ($incident->risk_score >= 50 ? 'text-orange-500' : 'text-emerald-500') }}">{{ $incident->risk_score }}</span>
                <span class="text-2xl text-slate-500 font-bold">/100</span>
            </div>
            <div class="text-lg font-bold uppercase tracking-widest {{ $incident->severity === 'critical' ? 'text-rose-500' : ($incident->severity === 'high' ? 'text-orange-500' : 'text-yellow-500') }} mb-4">
                {{ $incident->severity }}
            </div>
            
            <div class="inline-block px-3 py-1 bg-slate-950 rounded border border-slate-800 text-xs text-slate-400 font-mono">
                {{ $incident->detection_rule }}
            </div>
        </div>

        <!-- Incident Information -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
            <div class="p-4 border-b border-slate-800 bg-slate-950/50">
                <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Incident Information</h2>
            </div>
            <div class="p-6">
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-slate-500 text-xs mb-1">Source IP</dt>
                        <dd class="text-slate-200 font-mono bg-slate-950 px-2 py-1 rounded inline-block">{{ $incident->source_ip ?? 'Unknown' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 text-xs mb-1">Target / Username</dt>
                        <dd class="text-slate-200 font-medium">{{ $incident->username ?? 'Unknown' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 text-xs mb-1">Affected Host</dt>
                        <dd class="text-slate-200">{{ $incident->agent->hostname ?? 'Unknown' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 text-xs mb-1">First Seen</dt>
                        <dd class="text-slate-300">{{ $incident->first_seen_at->format('10 M Y H:i:s') }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 text-xs mb-1">Last Seen</dt>
                        <dd class="text-slate-300">{{ $incident->last_seen_at->format('10 M Y H:i:s') }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 text-xs mb-1">Duration</dt>
                        <dd class="text-slate-300">{{ $incident->first_seen_at->diffForHumans($incident->last_seen_at, true) }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Assignment -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl p-6">
            <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-4">Assignment</h2>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-400 font-bold border border-blue-500/30">
                    {{ substr($incident->assignedLead->name ?? '?', 0, 1) }}
                </div>
                <div>
                    <p class="text-sm font-medium text-white">{{ $incident->assignedLead->name ?? 'Unassigned' }}</p>
                    <p class="text-xs text-slate-500">Lead Investigator</p>
                </div>
            </div>
            <button onclick="document.getElementById('modal-assign').classList.remove('hidden')" class="w-full py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-sm transition">Change Assignment</button>
        </div>
    </div>
</div>

<!-- Modals -->

<!-- Add Response Modal -->
<div id="modal-response" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="p-4 border-b border-slate-800 flex justify-between items-center bg-slate-950/50">
            <h3 class="font-bold text-white">Add Response Action</h3>
            <button onclick="document.getElementById('modal-response').classList.add('hidden')" class="text-slate-500 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('security.incidents.responses.store', $incident) }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Action Name</label>
                <input type="text" name="action" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-white focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. Blocked IP Address" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Description</label>
                <textarea name="description" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-white focus:ring-blue-500 focus:border-blue-500 h-24" placeholder="Detail the action taken..." required></textarea>
            </div>
            <div class="flex justify-end pt-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-sm font-semibold transition">Save Response</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Evidence Modal -->
<div id="modal-evidence" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="p-4 border-b border-slate-800 flex justify-between items-center bg-slate-950/50">
            <h3 class="font-bold text-white">Add Evidence</h3>
            <button onclick="document.getElementById('modal-evidence').classList.add('hidden')" class="text-slate-500 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('security.incidents.evidence.store', $incident) }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Type</label>
                <select name="type" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-white focus:ring-blue-500 focus:border-blue-500">
                    <option value="LOG">Log Snippet</option>
                    <option value="COMMAND_OUTPUT">Command Output</option>
                    <option value="NETWORK_CAPTURE">Network Capture</option>
                    <option value="DOCUMENT">Document</option>
                    <option value="OTHER">Other</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Title</label>
                <input type="text" name="title" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-white focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. auth.log snippet" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Content / Description</label>
                <textarea name="description" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-white focus:ring-blue-500 focus:border-blue-500 h-24 font-mono text-xs" placeholder="Paste log lines or description here..."></textarea>
            </div>
            <div class="flex justify-end pt-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-sm font-semibold transition">Save Evidence</button>
            </div>
        </form>
    </div>
</div>

<!-- Resolve Incident Modal -->
<div id="modal-resolve" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
        <div class="p-4 border-b border-slate-800 flex justify-between items-center bg-slate-950/50">
            <h3 class="font-bold text-white">Resolve Security Incident</h3>
            <button onclick="document.getElementById('modal-resolve').classList.add('hidden')" class="text-slate-500 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('security.incidents.resolve', $incident) }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Resolution Type</label>
                <select name="resolution_type" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-white focus:ring-blue-500 focus:border-blue-500">
                    <option value="NO_COMPROMISE">No Compromise Detected</option>
                    <option value="COMPROMISED_MITIGATED">Compromised but Mitigated</option>
                    <option value="FALSE_POSITIVE">False Positive</option>
                    <option value="DUPLICATE">Duplicate</option>
                    <option value="OTHER">Other</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Root Cause</label>
                <input type="text" name="root_cause" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-white focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. Internet exposed SSH endpoint" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Final Assessment & Summary</label>
                <textarea name="resolution_summary" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-white focus:ring-blue-500 focus:border-blue-500 h-24" placeholder="Summarize the investigation findings and actions taken..." required></textarea>
            </div>
            <div class="flex items-center gap-2 mt-4 mb-4">
                <input type="checkbox" required class="w-4 h-4 rounded border-slate-700 bg-slate-900 text-blue-600 focus:ring-blue-600 focus:ring-offset-slate-900">
                <label class="text-sm text-slate-300">Investigation completed and verified</label>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('modal-resolve').classList.add('hidden')" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-semibold transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm font-semibold transition">Resolve Incident</button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Modal -->
<div id="modal-assign" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
        <div class="p-4 border-b border-slate-800 flex justify-between items-center bg-slate-950/50">
            <h3 class="font-bold text-white">Assign Investigator</h3>
            <button onclick="document.getElementById('modal-assign').classList.add('hidden')" class="text-slate-500 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('security.incidents.assign', $incident) }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Select User</label>
                <select name="user_id" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-white focus:ring-blue-500 focus:border-blue-500">
                    @foreach(\App\Models\User::all() as $user)
                        <option value="{{ $user->id }}" {{ $incident->assigned_lead_id == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->role }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end pt-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-sm font-semibold transition">Save Assignment</button>
            </div>
        </form>
    </div>
</div>

@endsection
