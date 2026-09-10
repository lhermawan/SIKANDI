@extends('layouts.app')

@section('title', 'Audit Trail & Log Aktivitas')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Administrasi</span>
                <span>/</span>
                <span class="text-white">Audit Log</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Audit Trail & Log Aktivitas Sistem</h1>
            <p class="text-xs text-slate-400 mt-0.5">Rekam jejak immutable seluruh perubahan data aset, konfigurasi CI, tiket, dan insiden persandian</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-4">
        <form action="{{ route('admin.audit-logs') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <div class="lg:col-span-2 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama record, pengguna, IP address..."
                    class="w-full pl-9 pr-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <div>
                <select name="action" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Aksi</option>
                    <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>CREATED</option>
                    <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>UPDATED</option>
                    <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>DELETED</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-medium transition cursor-pointer text-center">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'action', 'module']))
                    <a href="{{ route('admin.audit-logs') }}" class="py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition text-center">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 text-slate-400 uppercase tracking-wider border-b border-slate-800/80">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Waktu (WIB)</th>
                        <th class="py-3 px-4 font-semibold">Aksi</th>
                        <th class="py-3 px-4 font-semibold">Modul</th>
                        <th class="py-3 px-4 font-semibold">Deskripsi Entitas / Record</th>
                        <th class="py-3 px-4 font-semibold">Pengguna</th>
                        <th class="py-3 px-4 font-semibold">IP & Perangkat</th>
                        <th class="py-3 px-4 font-semibold text-center">Perubahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($logs as $log)
                        @php
                            $badgeColor = match($log->action) {
                                'created' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                'updated' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                                'deleted' => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
                                default => 'bg-slate-500/10 text-slate-400 border-slate-500/30'
                            };
                        @endphp
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-400 whitespace-nowrap">
                                {{ $log->created_at ? $log->created_at->isoFormat('D MMM Y, HH:mm:ss') : '-' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase {{ $badgeColor }}">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-mono text-slate-300">{{ $log->module }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-white">{{ $log->record_name ?? 'ID: '.$log->record_id }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">Record ID: #{{ $log->record_id }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-slate-200">{{ $log->user_name ?? ($log->user ? $log->user->name : 'System') }}</div>
                                @if($log->user)
                                    <div class="text-[10px] text-slate-400">&#64;{{ $log->user->username }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-400 font-mono text-[11px]">
                                <div>{{ $log->ip_address ?? '127.0.0.1' }}</div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($log->new_values || $log->old_values)
                                    <button type="button" onclick="showDiffModal({{ $log->id }}, {{ json_encode($log->old_values) }}, {{ json_encode($log->new_values) }})" 
                                        class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-blue-400 rounded-lg text-[11px] font-medium transition cursor-pointer">
                                        Detail Diff
                                    </button>
                                @else
                                    <span class="text-slate-600 text-[11px]">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">
                                Belum ada riwayat audit log tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-800/80">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    <!-- Diff Modal -->
    <div id="diffModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-2xl w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Inspeksi Nilai Audit Log <span id="diffLogId" class="text-blue-400 font-mono"></span></h3>
                <button type="button" onclick="document.getElementById('diffModal').classList.add('hidden')" class="text-slate-400 hover:text-white cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-mono">
                <div>
                    <div class="font-semibold text-rose-400 mb-1.5 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> Nilai Lama (Old Values)
                    </div>
                    <pre id="diffOldValues" class="p-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-300 text-[11px] overflow-auto max-h-60"></pre>
                </div>
                <div>
                    <div class="font-semibold text-emerald-400 mb-1.5 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Nilai Baru (New Values)
                    </div>
                    <pre id="diffNewValues" class="p-3 bg-slate-950 border border-slate-800 rounded-xl text-slate-300 text-[11px] overflow-auto max-h-60"></pre>
                </div>
            </div>
            <div class="flex justify-end pt-3 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('diffModal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-medium cursor-pointer">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function showDiffModal(id, oldVals, newVals) {
    document.getElementById('diffLogId').textContent = '#' + id;
    document.getElementById('diffOldValues').textContent = oldVals ? JSON.stringify(oldVals, null, 2) : '(Kosong / Baru)';
    document.getElementById('diffNewValues').textContent = newVals ? JSON.stringify(newVals, null, 2) : '(Dihapus)';
    document.getElementById('diffModal').classList.remove('hidden');
}
</script>
@endsection
