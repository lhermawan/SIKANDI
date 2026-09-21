@extends('layouts.app')

@section('title', 'Insiden Keamanan Informasi (CSIRT)')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Security & CSIRT</span>
                <span>/</span>
                <span class="text-white">Insiden Siber</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Tanggap Darurat Insiden Siber (CSIRT Ciamis)</h1>
            <p class="text-xs text-slate-400 mt-0.5">Penanganan insiden malware, web defacement, kebocoran data, phising, dan peretasan akun</p>
        </div>
        <div>
            <a href="{{ route('security.incidents.create') }}" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-lg shadow-red-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Lapor Insiden Siber Baru</span>
            </a>
        </div>
    </div>

    {{-- Smart Filter & Bulk Actions --}}
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-xl">
        {{-- Search & Filters --}}
        <form method="GET" action="{{ route('security.incidents.index') }}" class="flex-1 w-full flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari insiden..." class="w-full bg-slate-950 border border-slate-700 rounded-lg pl-9 pr-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
            </div>
            
            <div class="flex gap-2 w-full sm:w-auto">
                <select name="status" class="bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-300 focus:outline-none focus:border-blue-500 min-w-[140px]">
                    <option value="">Semua Status</option>
                    <option value="reported" {{ request('status') === 'reported' ? 'selected' : '' }}>Reported</option>
                    <option value="investigating" {{ request('status') === 'investigating' ? 'selected' : '' }}>Investigating</option>
                    <option value="contained" {{ request('status') === 'contained' ? 'selected' : '' }}>Contained</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
                
                <select name="severity" class="bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-300 focus:outline-none focus:border-blue-500 min-w-[120px]">
                    <option value="">Semua Level</option>
                    <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Critical</option>
                    <option value="high" {{ request('severity') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ request('severity') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ request('severity') === 'low' ? 'selected' : '' }}>Low</option>
                </select>

                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shrink-0">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'status', 'severity']))
                    <a href="{{ route('security.incidents.index') }}" class="bg-slate-800 hover:bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shrink-0">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        {{-- Bulk Action --}}
        <div class="flex shrink-0">
            <form id="bulk-delete-form" action="{{ route('security.incidents.bulk-destroy') }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
                <input type="hidden" name="ids" id="bulk-delete-ids">
            </form>
            <button type="button" id="btn-bulk-delete" class="hidden px-4 py-2 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/50 text-rose-400 rounded-lg text-sm font-semibold transition items-center gap-2" onclick="confirmBulkDelete()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Hapus Terpilih (<span id="selected-count">0</span>)
            </button>
        </div>
    </div>

    <!-- Security Incidents Table -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4 w-10">
                            <input type="checkbox" id="check-all" class="rounded bg-slate-800 border-slate-600 text-blue-500 focus:ring-blue-500/30">
                        </th>
                        <th class="py-3 px-4">Kode CSIRT</th>
                        <th class="py-3 px-4">Judul & Jenis Serangan</th>
                        <th class="py-3 px-4">OPD Korban</th>
                        <th class="py-3 px-4">CI Terkait</th>
                        <th class="py-3 px-4">Tingkat Keparahan</th>
                        <th class="py-3 px-4">Risk Score</th>
                        <th class="py-3 px-4">Alur Kerja (Workflow)</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($incidents as $sec)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-4">
                                <input type="checkbox" class="row-checkbox rounded bg-slate-800 border-slate-600 text-blue-500 focus:ring-blue-500/30" value="{{ $sec->id }}">
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-rose-400">
                                {{ $sec->incident_code }}
                            </td>
                            <td class="py-3.5 px-4 max-w-xs truncate">
                                <a href="{{ route('security.incidents.show', $sec) }}" class="text-slate-200 font-medium hover:text-blue-400 block truncate">
                                    {{ $sec->title }}
                                </a>
                                <span class="text-xs text-slate-500 block truncate">
                                    {{ str_replace('_', ' ', strtoupper($sec->incident_type)) }} 
                                    @if($sec->detection_rule) • Rule: {{ $sec->detection_rule }} @endif
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-slate-200 font-medium">{{ $sec->organization->name }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($sec->configurationItem)
                                    <a href="{{ route('cmdb.show', $sec->configurationItem) }}" class="font-mono text-blue-400 hover:underline">
                                        {{ $sec->configurationItem->ci_code }}
                                    </a>
                                @else
                                    <span class="text-slate-600">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    @if($sec->severity === 'critical') bg-rose-500/20 text-rose-300
                                    @elseif($sec->severity === 'high') bg-orange-500/20 text-orange-300
                                    @else bg-yellow-500/20 text-yellow-300 @endif">
                                    {{ $sec->severity }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-xs {{ $sec->risk_score >= 80 ? 'text-rose-400' : ($sec->risk_score >= 50 ? 'text-orange-400' : 'text-emerald-400') }}">
                                    {{ $sec->risk_score ?? 0 }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-slate-800 text-slate-300">
                                    {{ $sec->workflow_status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <span class="text-slate-400 text-xs">{{ $sec->created_at->format('d M Y') }}</span>
                                    <form action="{{ route('security.incidents.destroy', $sec) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus insiden keamanan siber ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-rose-500/70 hover:text-rose-500 rounded transition" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-500">Tidak ada insiden keamanan informasi aktif yang sesuai kriteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($incidents->hasPages())
            <div class="px-6 py-4 border-t border-slate-800">
                {{ $incidents->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll = document.getElementById('check-all');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const btnBulkDelete = document.getElementById('btn-bulk-delete');
        const selectedCountSpan = document.getElementById('selected-count');
        
        function updateBulkButton() {
            const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
            selectedCountSpan.textContent = checkedCount;
            if (checkedCount > 0) {
                btnBulkDelete.classList.remove('hidden');
                btnBulkDelete.classList.add('flex');
            } else {
                btnBulkDelete.classList.add('hidden');
                btnBulkDelete.classList.remove('flex');
                checkAll.checked = false;
            }
        }
        
        if (checkAll) {
            checkAll.addEventListener('change', function() {
                rowCheckboxes.forEach(cb => {
                    cb.checked = this.checked;
                });
                updateBulkButton();
            });
        }
        
        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkButton);
        });
        
        window.confirmBulkDelete = function() {
            const selected = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
            if (selected.length === 0) return;
            
            if (confirm(`Apakah Anda yakin ingin menghapus ${selected.length} insiden yang dipilih?`)) {
                document.getElementById('bulk-delete-ids').value = selected.join(',');
                document.getElementById('bulk-delete-form').submit();
            }
        };
    });
</script>
