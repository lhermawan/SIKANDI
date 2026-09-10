@extends('layouts.app')

@section('title', 'Website & SSL Monitoring')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Monitoring</span>
                <span>/</span>
                <span class="text-white">Website & SSL</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Website & SSL Health Monitoring</h1>
            <p class="text-xs text-slate-400 mt-0.5">Pemantauan ketersediaan (uptime), latensi response, dan masa berlaku sertifikat SSL portal OPD Ciamis</p>
        </div>
        <div>
            <button onclick="document.getElementById('addWebsiteModal').classList.remove('hidden')" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-lg shadow-blue-600/30 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Website</span>
            </button>
        </div>
    </div>

    <!-- Feedback messages -->
    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 rounded-xl text-xs flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Dashboard Cards (matching Section 13) -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-center">
            <span class="text-[10px] text-slate-400 uppercase font-semibold">Total Website</span>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-center">
            <span class="text-[10px] text-emerald-400 uppercase font-semibold">Website UP</span>
            <p class="text-2xl font-bold text-emerald-400 mt-1">{{ $stats['up'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-center">
            <span class="text-[10px] text-rose-400 uppercase font-semibold">Website DOWN</span>
            <p class="text-2xl font-bold {{ $stats['down'] > 0 ? 'text-rose-400' : 'text-slate-500' }} mt-1">{{ $stats['down'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-center">
            <span class="text-[10px] text-amber-400 uppercase font-semibold">Peringatan SSL</span>
            <p class="text-2xl font-bold {{ $stats['ssl_warning'] > 0 ? 'text-amber-400' : 'text-slate-500' }} mt-1">{{ $stats['ssl_warning'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-center col-span-2 md:col-span-1">
            <span class="text-[10px] text-slate-400 uppercase font-semibold">Rerata Respon</span>
            <p class="text-2xl font-bold text-cyan-400 mt-1">{{ $stats['avg_response_time'] }} <span class="text-xs font-normal">ms</span></p>
        </div>
    </div>

    <!-- Websites Table -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Nama Website & URL</th>
                        <th class="py-3 px-4">OPD Pengelola</th>
                        <th class="py-3 px-4">CI Terkait (CMDB)</th>
                        <th class="py-3 px-4 text-center">Status Uptime</th>
                        <th class="py-3 px-4">Response Time</th>
                        <th class="py-3 px-4">Sertifikat SSL</th>
                        <th class="py-3 px-4">Pengecekan Terakhir</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($websites as $site)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-4">
                                <p class="font-semibold text-white text-sm">{{ $site->name }}</p>
                                <a href="{{ $site->url }}" target="_blank" class="text-blue-400 hover:underline font-mono text-[11px] flex items-center gap-1 mt-0.5">
                                    <span>{{ $site->url }}</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-slate-200 font-medium">{{ $site->organization->name }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('cmdb.show', $site->configurationItem) }}" class="font-mono text-blue-400 hover:underline font-semibold block">
                                    {{ $site->configurationItem->ci_code }}
                                </a>
                                <span class="text-[10px] text-slate-400">{{ $site->configurationItem->name }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase inline-flex items-center gap-1.5
                                    @if($site->current_status === 'up') bg-emerald-500/20 text-emerald-300 border border-emerald-500/30
                                    @elseif($site->current_status === 'down') bg-rose-500/20 text-rose-300 border border-rose-500/30 animate-pulse
                                    @else bg-amber-500/20 text-amber-300 border border-amber-500/30 @endif">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $site->current_status === 'up' ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                                    {{ strtoupper($site->current_status) }}
                                </span>
                                @if($site->http_status_code)
                                    <span class="block text-[10px] text-slate-500 mt-0.5">HTTP {{ $site->http_status_code }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                @if($site->response_time_ms)
                                    <span class="text-white font-semibold">{{ $site->response_time_ms }} ms</span>
                                @else
                                    <span class="text-slate-600">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    @if($site->ssl_status === 'valid') bg-emerald-500/20 text-emerald-300
                                    @elseif($site->ssl_status === 'expiring_soon') bg-amber-500/20 text-amber-300
                                    @elseif($site->ssl_status === 'expired') bg-rose-500/20 text-rose-300
                                    @else bg-slate-800 text-slate-400 @endif">
                                    SSL: {{ strtoupper($site->ssl_status) }}
                                </span>
                                @if($site->ssl_expires_at)
                                    <span class="block text-[10px] text-slate-400 mt-0.5">Expired: {{ $site->ssl_expires_at->format('d M Y') }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-400 text-[11px]">
                                {{ $site->last_checked_at ? $site->last_checked_at->diffForHumans() : 'Belum dicek' }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <form action="{{ route('monitoring.websites.check', $site) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-medium transition cursor-pointer flex items-center gap-1.5 shadow">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        <span>Cek Sekarang</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500">Belum ada website terdaftar dalam monitoring.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Website -->
    <div id="addWebsiteModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Tambah Website ke Monitoring</h3>
                <button type="button" onclick="document.getElementById('addWebsiteModal').classList.add('hidden')" class="text-slate-400 hover:text-white cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('monitoring.websites.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 gap-3">
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">Nama Website *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. Website Resmi DINKES">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">URL (dengan https://) *</label>
                        <input type="url" name="url" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="https://dinkes.ciamiskab.go.id">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">OPD Pengelola *</label>
                        <select name="organization_id" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih OPD --</option>
                            @foreach($organizations as $org)
                                <option value="{{ $org->id }}">{{ $org->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">Terkait CMDB (CI) *</label>
                        <select name="ci_id" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Configuration Item --</option>
                            @foreach($configurationItems as $ci)
                                <option value="{{ $ci->id }}">[{{ $ci->ci_code }}] {{ $ci->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" onclick="document.getElementById('addWebsiteModal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-medium cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-semibold shadow-md shadow-blue-600/30 cursor-pointer">Simpan Website</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
