@extends('layouts.app')

@section('title', 'Service Desk (Tiket Layanan)')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Layanan IT</span>
                <span>/</span>
                <span class="text-white">Service Desk</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Service Desk & Tiket Aduan</h1>
            <p class="text-xs text-slate-400 mt-0.5">Pusat permohonan layanan TIK, aduan gangguan, dan insiden persandian OPD</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('service-desk.tickets.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-lg shadow-blue-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Buka Tiket Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-4">
        <form action="{{ route('service-desk.tickets') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <div class="lg:col-span-2 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor tiket, judul, deskripsi..."
                    class="w-full pl-9 pr-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <div>
                <select name="status" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200">
                    <option value="">Semua Status</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open (Baru)</option>
                    <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Assigned (Ditugaskan)</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress (Dikerjakan)</option>
                    <option value="waiting" {{ request('status') == 'waiting' ? 'selected' : '' }}>Waiting (Menunggu Respon)</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved (Selesai)</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed (Ditutup)</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-medium transition cursor-pointer text-center">
                    Terapkan
                </button>
                @if(request()->anyFilled(['search', 'status', 'priority']))
                    <a href="{{ route('service-desk.tickets') }}" class="py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition text-center">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tickets Table -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Nomor Tiket</th>
                        <th class="py-3 px-4">Judul & Pemohon</th>
                        <th class="py-3 px-4">OPD</th>
                        <th class="py-3 px-4">Kategori & Prioritas</th>
                        <th class="py-3 px-4">Teknisi Bertugas</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-blue-400">
                                <a href="{{ route('service-desk.tickets.show', $ticket) }}" class="hover:underline">
                                    {{ $ticket->ticket_number }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4">
                                <p class="font-semibold text-white text-sm">{{ $ticket->title }}</p>
                                <p class="text-[11px] text-slate-400">Pemohon: {{ $ticket->requester->name }} &bull; {{ $ticket->created_at->diffForHumans() }}</p>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-slate-200 font-medium">{{ $ticket->organization->name }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] bg-slate-800 text-slate-300 border border-slate-700 block w-max mb-1">
                                    {{ ucwords(str_replace('_', ' ', $ticket->category)) }}
                                </span>
                                <span class="text-[10px] font-bold uppercase
                                    @if($ticket->priority === 'critical') text-rose-400
                                    @elseif($ticket->priority === 'high') text-orange-400
                                    @elseif($ticket->priority === 'medium') text-yellow-400
                                    @else text-slate-400 @endif">
                                    {{ $ticket->priority }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-slate-300">{{ $ticket->assignedTechnician?->name ?? 'Belum Ditugaskan' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase
                                    @if($ticket->status === 'open') bg-blue-500/20 text-blue-300 border border-blue-500/30
                                    @elseif($ticket->status === 'assigned') bg-indigo-500/20 text-indigo-300 border border-indigo-500/30
                                    @elseif($ticket->status === 'in_progress') bg-amber-500/20 text-amber-300 border border-amber-500/30
                                    @elseif($ticket->status === 'resolved') bg-emerald-500/20 text-emerald-300 border border-emerald-500/30
                                    @else bg-slate-700 text-slate-300 @endif">
                                    {{ strtoupper($ticket->status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('service-desk.tickets.show', $ticket) }}" class="p-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded transition" title="Buka Tiket">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    @hasanyrole('Super Admin|Admin Persandian|IT Technician')
                                        <a href="{{ route('service-desk.tickets.edit', $ticket) }}" class="p-1.5 bg-slate-800 hover:bg-blue-600 hover:text-white text-slate-300 rounded transition" title="Edit Tiket">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <form action="{{ route('service-desk.tickets.destroy', $ticket) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus tiket ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 bg-slate-800 hover:bg-rose-600 hover:text-white text-slate-300 rounded transition" title="Hapus Tiket">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endhasanyrole
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">Belum ada tiket yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
