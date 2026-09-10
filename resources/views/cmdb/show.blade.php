@extends('layouts.app')

@section('title', $cmdb->ci_code . ' — ' . $cmdb->name)

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('cmdb.index') }}" class="hover:underline">CMDB</a>
                <span>/</span>
                <span class="text-white">{{ $cmdb->ci_code }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-white tracking-tight">{{ $cmdb->name }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase
                    @if($cmdb->status === 'active') bg-emerald-500/20 text-emerald-300 border border-emerald-500/30
                    @elseif($cmdb->status === 'maintenance') bg-indigo-500/20 text-indigo-300 border border-indigo-500/30
                    @elseif($cmdb->status === 'warning') bg-amber-500/20 text-amber-300 border border-amber-500/30
                    @elseif($cmdb->status === 'down') bg-rose-500/20 text-rose-300 border border-rose-500/30
                    @else bg-slate-700 text-slate-300 @endif">
                    {{ $cmdb->status }}
                </span>
                <span class="text-xs font-bold uppercase px-2 py-0.5 rounded
                    @if($cmdb->criticality === 'critical') bg-red-500/20 text-red-300 border border-red-500/30
                    @elseif($cmdb->criticality === 'high') bg-orange-500/20 text-orange-300 border border-orange-500/30
                    @else bg-slate-800 text-slate-300 @endif">
                    Kritikalitas: {{ $cmdb->criticality }}
                </span>
            </div>
            <p class="text-xs text-slate-400 font-mono mt-1">
                CI Code: <span class="text-blue-400 font-bold">{{ $cmdb->ci_code }}</span> &bull;
                Tipe: <span class="text-slate-200">{{ $cmdb->ciType->name }}</span> &bull;
                Env: <span class="text-slate-200 uppercase">{{ $cmdb->environment }}</span> &bull;
                OPD: <span class="text-slate-200">{{ $cmdb->organization->name }}</span>
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="openRelModal()" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-lg shadow-indigo-600/30 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                <span>Hubungkan Relasi CI</span>
            </button>
            <a href="{{ route('cmdb.edit', $cmdb) }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold transition border border-slate-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Data</span>
            </a>
            <a href="{{ route('cmdb.graph') }}" class="p-2 bg-slate-800 hover:bg-slate-700 text-emerald-400 rounded-xl transition border border-slate-700" title="Buka di Graph View">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </a>
        </div>
    </div>

    <!-- Multi-tab Grid Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Col 1 & 2: Technical Specs, Relationships, Monitoring, Incidents -->
        <div class="lg:col-span-2 space-y-6">
            <!-- 1. General & Technical Information -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5">
                <h2 class="font-bold text-white text-sm mb-4 flex items-center gap-2 pb-2 border-b border-slate-800">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>General & Technical Information</span>
                </h2>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-4 gap-x-6 text-xs">
                    <div>
                        <span class="text-slate-500 block text-[11px]">Hostname</span>
                        <span class="font-mono text-white font-medium">{{ $cmdb->hostname ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[11px]">IP Address</span>
                        <span class="font-mono text-cyan-400 font-semibold">{{ $cmdb->ip_address ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[11px]">MAC Address</span>
                        <span class="font-mono text-slate-300">{{ $cmdb->mac_address ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[11px]">Domain / URL</span>
                        @if($cmdb->url)
                            <a href="{{ $cmdb->url }}" target="_blank" class="text-blue-400 hover:underline truncate block">{{ $cmdb->url }}</a>
                        @else
                            <span class="text-slate-300">{{ $cmdb->domain ?? '-' }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[11px]">Operating System</span>
                        <span class="text-white">{{ $cmdb->operating_system ?? '-' }} {{ $cmdb->os_version ?? '' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[11px]">Nomor Seri</span>
                        <span class="font-mono text-slate-300">{{ $cmdb->serial_number ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[11px]">Lokasi Ruang</span>
                        <span class="text-slate-300">{{ $cmdb->location?->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[11px]">Penanggung Jawab</span>
                        <span class="text-slate-300">{{ $cmdb->responsible_unit ?? $cmdb->owner_person ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[11px]">Aset Fisik ITAM Terkait</span>
                        @if($cmdb->asset)
                            <a href="{{ route('itam.show', $cmdb->asset) }}" class="font-mono text-amber-400 hover:underline font-semibold">
                                {{ $cmdb->asset->asset_number }} ({{ $cmdb->asset->name }})
                            </a>
                        @else
                            <span class="text-slate-500 italic">Tidak terhubung fisik</span>
                        @endif
                    </div>
                </div>

                @if($cmdb->specifications && count($cmdb->specifications) > 0)
                    <div class="mt-4 pt-3 border-t border-slate-800">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Spesifikasi Hardware / Resource</span>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach($cmdb->specifications as $key => $val)
                                <div class="p-2 bg-slate-950/60 border border-slate-800/80 rounded-xl text-xs">
                                    <span class="text-slate-500 block text-[10px]">{{ $key }}</span>
                                    <span class="font-medium text-slate-200">{{ $val }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($cmdb->description)
                    <div class="mt-4 pt-3 border-t border-slate-800 text-xs">
                        <span class="text-slate-500 block text-[11px] mb-1">Deskripsi</span>
                        <p class="text-slate-300 leading-relaxed">{{ $cmdb->description }}</p>
                    </div>
                @endif
            </div>

            <!-- 2. CMDB Relationships (Bidirectional) -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-800">
                    <div>
                        <h2 class="font-bold text-white text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            <span>CMDB Relationships (Relasi Antar Komponen)</span>
                        </h2>
                        <p class="text-xs text-slate-400">Keterhubungan dua arah dengan server, aplikasi, database, jaringan, dan firewall</p>
                    </div>
                    <button type="button" onclick="openRelModal()" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold flex items-center gap-1">
                        + Tambah Relasi
                    </button>
                </div>

                <div class="space-y-3">
                    @forelse($relationships as $rel)
                        @php $otherCi = $rel['related_ci']; @endphp
                        <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center shrink-0">
                                    @if($rel['direction'] === 'outbound')
                                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    @else
                                        <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                    @endif
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-indigo-300">{{ $rel['display_label'] }}</span>
                                        <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-800 text-slate-400 border border-slate-700 uppercase">{{ $otherCi->ciType->name }}</span>
                                    </div>
                                    <a href="{{ route('cmdb.show', $otherCi) }}" class="text-sm font-bold text-white hover:text-blue-400 hover:underline transition">
                                        {{ $otherCi->name }} ({{ $otherCi->ci_code }})
                                    </a>
                                    @if($rel['description'])
                                        <p class="text-xs text-slate-400 mt-0.5">{{ $rel['description'] }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('cmdb.show', $otherCi) }}" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded text-xs">
                                    Buka CI
                                </a>
                                @if($rel['direction'] === 'outbound')
                                    <form action="{{ route('cmdb.relationships.destroy', $rel['id']) }}" method="POST" onsubmit="return confirm('Hapus relasi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-slate-500 hover:text-rose-400 rounded transition" title="Hapus relasi">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 border border-dashed border-slate-800 rounded-xl">
                            <p class="text-xs text-slate-500">Belum ada relasi yang terhubung dengan CI ini.</p>
                            <button type="button" onclick="openRelModal()" class="mt-2 text-xs text-indigo-400 hover:underline font-medium">
                                + Hubungkan Relasi Sekarang
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- 3. Monitoring Information -->
            @if($cmdb->website)
                <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5">
                    <h2 class="font-bold text-white text-sm mb-4 flex items-center gap-2 pb-2 border-b border-slate-800">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Website & SSL Health Monitoring</span>
                    </h2>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                        <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl">
                            <span class="text-slate-500 block text-[10px]">Status Uptime</span>
                            <span class="text-lg font-bold text-emerald-400 uppercase">{{ $cmdb->website->current_status }}</span>
                            <span class="block text-[10px] text-slate-500">HTTP Code: {{ $cmdb->website->http_status_code ?? 200 }}</span>
                        </div>
                        <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl">
                            <span class="text-slate-500 block text-[10px]">Response Time</span>
                            <span class="text-lg font-bold text-white">{{ $cmdb->website->response_time_ms ?? 0 }} ms</span>
                            <span class="block text-[10px] text-slate-500">Latency rata-rata</span>
                        </div>
                        <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl">
                            <span class="text-slate-500 block text-[10px]">Status Sertifikat SSL</span>
                            <span class="text-lg font-bold text-cyan-400 uppercase">{{ $cmdb->website->ssl_status }}</span>
                            <span class="block text-[10px] text-slate-500">{{ $cmdb->website->ssl_issuer ?? "Let's Encrypt" }}</span>
                        </div>
                        <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl">
                            <span class="text-slate-500 block text-[10px]">Kadaluarsa SSL</span>
                            <span class="text-sm font-bold text-slate-200">{{ $cmdb->website->ssl_expires_at?->format('d M Y') ?? 'N/A' }}</span>
                            <span class="block text-[10px] text-emerald-400">{{ $cmdb->website->ssl_expires_at ? $cmdb->website->ssl_expires_at->diffForHumans() : '' }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- 4. Related Incidents & Tickets -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5">
                <h2 class="font-bold text-white text-sm mb-4 flex items-center gap-2 pb-2 border-b border-slate-800">
                    <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Insiden & Tiket Terkait CI Ini</span>
                </h2>

                <div class="space-y-3">
                    @forelse($cmdb->incidents as $inc)
                        <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl flex items-center justify-between text-xs">
                            <div>
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="font-mono font-bold text-rose-400">{{ $inc->incident_number }}</span>
                                    <span class="text-[10px] px-2 py-0.2 rounded font-bold uppercase bg-rose-500/20 text-rose-300">{{ $inc->priority }}</span>
                                    <span class="text-slate-500">{{ $inc->detected_at->format('d M Y H:i') }}</span>
                                </div>
                                <p class="text-white font-medium">{{ $inc->title }}</p>
                                <p class="text-slate-400 text-[11px]">Teknisi: {{ $inc->assignedTechnician?->name ?? 'Belum Ditugaskan' }}</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-slate-800 text-slate-300">
                                {{ $inc->status }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 text-center py-3">Tidak ada riwayat insiden tercatat pada CI ini.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Col 3: Changes & Audit Timeline -->
        <div class="space-y-6">
            <!-- Timeline of Changes -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5">
                <h2 class="font-bold text-white text-sm mb-4 flex items-center gap-2 pb-2 border-b border-slate-800">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Activity & Audit Timeline</span>
                </h2>

                <div class="relative pl-6 space-y-4 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-800">
                    @forelse($timeline as $log)
                        <div class="relative text-xs">
                            <div class="absolute -left-6 top-1 w-2.5 h-2.5 rounded-full border-2 border-slate-900
                                @if($log->action === 'created') bg-emerald-400
                                @elseif($log->action === 'updated') bg-blue-400
                                @else bg-slate-400 @endif">
                            </div>
                            <div class="flex items-center justify-between text-[10px] text-slate-500">
                                <span class="font-semibold text-slate-300">{{ $log->user_name ?? 'System' }}</span>
                                <span>{{ $log->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-slate-200 mt-0.5">
                                <span class="uppercase text-[9px] font-bold px-1 py-0.2 rounded
                                    @if($log->action === 'created') bg-emerald-500/20 text-emerald-300
                                    @elseif($log->action === 'updated') bg-blue-500/20 text-blue-300
                                    @else bg-slate-800 text-slate-400 @endif">
                                    {{ $log->action }}
                                </span>
                                Record: {{ $log->record_name }}
                            </p>

                            @if($log->old_values && $log->new_values)
                                <div class="mt-1 p-2 rounded bg-slate-950/80 border border-slate-800/80 font-mono text-[10px] space-y-0.5">
                                    @foreach($log->new_values as $key => $newVal)
                                        @if(isset($log->old_values[$key]) && $log->old_values[$key] != $newVal)
                                            <div class="text-slate-400">
                                                <span class="text-slate-500">{{ $key }}:</span>
                                                <span class="text-rose-400 line-through">{{ is_array($log->old_values[$key]) ? json_encode($log->old_values[$key]) : $log->old_values[$key] }}</span>
                                                &rarr;
                                                <span class="text-emerald-400 font-semibold">{{ is_array($newVal) ? json_encode($newVal) : $newVal }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 py-3">Belum ada aktivitas audit tercatat.</p>
                    @endforelse
                </div>
            </div>

            <!-- Documents & SOP Linked -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5">
                <h2 class="font-bold text-white text-sm mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Dokumen & SOP Terkait</span>
                </h2>
                <div class="space-y-2 text-xs">
                    @forelse($cmdb->documents as $doc)
                        <div class="p-2.5 bg-slate-950/60 border border-slate-800 rounded-xl flex items-center justify-between">
                            <div>
                                <span class="font-mono text-[10px] text-blue-400">{{ $doc->document_code }}</span>
                                <p class="font-medium text-white">{{ $doc->title }}</p>
                            </div>
                            <span class="text-[10px] text-slate-500">v{{ $doc->version }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 py-2">Belum ada dokumen SOP terlampir.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Hubungkan Relasi CI -->
<div id="relModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg shadow-2xl p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-800">
            <div>
                <h3 class="font-bold text-white text-base">Hubungkan Relasi CMDB Baru</h3>
                <p class="text-xs text-slate-400">Buat relasi logis dua arah antara CI ini dengan CI lain</p>
            </div>
            <button type="button" onclick="closeRelModal()" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form action="{{ route('cmdb.relationships.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-medium text-slate-300 mb-1">Source CI (Sumber)</label>
                <input type="text" readonly value="{{ $cmdb->ci_code }} — {{ $cmdb->name }}" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-slate-400 font-mono">
                <input type="hidden" name="source_ci_id" value="{{ $cmdb->id }}">
            </div>

            <div>
                <label class="block font-medium text-slate-300 mb-1">Jenis Relasi Logis *</label>
                <select name="relationship_type" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                    <option value="hosted_on">Hosted On (Dihosting Pada Server / VM)</option>
                    <option value="runs_on">Runs On (Berjalan Di Atas OS / Container)</option>
                    <option value="uses">Uses (Menggunakan Database / API / Service)</option>
                    <option value="depends_on">Depends On (Bergantung Pada Komponen)</option>
                    <option value="connects_to">Connects To (Terhubung Ke Switch / Router)</option>
                    <option value="protected_by">Protected By (Dilindungi Oleh Firewall / WAF)</option>
                    <option value="contains">Contains (Memuat / Berisi Sub-komponen)</option>
                    <option value="managed_by">Managed By (Dikelola Oleh)</option>
                    <option value="located_at">Located At (Berlokasi Di)</option>
                    <option value="supports">Supports (Mendukung Layanan)</option>
                    <option value="part_of">Part Of (Bagian Dari Cluster / Ruang)</option>
                </select>
            </div>

            <div>
                <label class="block font-medium text-slate-300 mb-1">Target CI (Tujuan) *</label>
                <select name="target_ci_id" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                    <option value="">-- Pilih Target CI --</option>
                    @foreach($allCis as $target)
                        <option value="{{ $target->id }}">
                            [{{ $target->ci_code }}] {{ $target->name }} ({{ $target->ciType->name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-medium text-slate-300 mb-1">Keterangan Relasi (Opsional)</label>
                <input type="text" name="description" placeholder="Contoh: Koneksi port uplink 10G / Database connection pool"
                    class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeRelModal()" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl hover:bg-slate-700 transition">Batal</button>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-medium transition cursor-pointer">Simpan Relasi</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRelModal() {
        document.getElementById('relModal').classList.remove('hidden');
    }
    function closeRelModal() {
        document.getElementById('relModal').classList.add('hidden');
    }
</script>
@endsection
