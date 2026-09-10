@extends('layouts.app')

@section('title', 'Tiket: ' . $ticket->ticket_number)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('service-desk.tickets') }}" class="hover:underline">Service Desk</a>
                <span>/</span>
                <span class="text-white font-mono">{{ $ticket->ticket_number }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-white tracking-tight">{{ $ticket->title }}</h1>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase
                    @if($ticket->status === 'open') bg-blue-500/20 text-blue-300 border border-blue-500/30
                    @elseif($ticket->status === 'assigned') bg-indigo-500/20 text-indigo-300 border border-indigo-500/30
                    @elseif($ticket->status === 'in_progress') bg-amber-500/20 text-amber-300 border border-amber-500/30
                    @elseif($ticket->status === 'resolved') bg-emerald-500/20 text-emerald-300 border border-emerald-500/30
                    @else bg-slate-700 text-slate-300 @endif">
                    {{ $ticket->status }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">
                Pemohon: <span class="text-white font-medium">{{ $ticket->requester->name }}</span> &bull;
                OPD: <span class="text-white font-medium">{{ $ticket->organization->name }}</span> &bull;
                Diajukan: <span>{{ $ticket->created_at->format('d M Y H:i') }}</span>
            </p>
        </div>
        <a href="{{ route('service-desk.tickets') }}" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium transition">
            &larr; Kembali ke Daftar Tiket
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Col 1 & 2: Ticket Description & Comments Feed -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Description Card -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Deskripsi Aduan</h2>
                <div class="text-sm text-slate-200 leading-relaxed whitespace-pre-line bg-slate-950/60 p-4 rounded-xl border border-slate-800">
                    {{ $ticket->description }}
                </div>

                @if($ticket->resolution_notes)
                    <div class="mt-4 p-4 rounded-xl bg-emerald-950/40 border border-emerald-500/30 text-xs">
                        <span class="font-bold text-emerald-300 block mb-1">Catatan Solusi / Penyelesaian:</span>
                        <p class="text-slate-200 whitespace-pre-line">{{ $ticket->resolution_notes }}</p>
                        <span class="block text-[10px] text-emerald-400 mt-2">Diselesaikan pada: {{ $ticket->resolved_at?->format('d M Y H:i') }}</span>
                    </div>
                @endif
            </div>

            <!-- Conversation / Comments Feed -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 space-y-4">
                <h2 class="text-sm font-bold text-white flex items-center justify-between">
                    <span>Percakapan & Catatan Tindak Lanjut</span>
                    <span class="text-xs text-slate-400 font-normal">{{ $ticket->comments->count() }} Komentar</span>
                </h2>

                <div class="space-y-3">
                    @forelse($ticket->comments as $comment)
                        <div class="p-4 rounded-xl {{ $comment->is_internal ? 'bg-amber-950/30 border border-amber-800/50' : 'bg-slate-950/60 border border-slate-800' }} text-xs">
                            <div class="flex items-center justify-between mb-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-white">{{ $comment->user->name }}</span>
                                    <span class="text-[10px] px-2 py-0.2 rounded bg-slate-800 text-slate-300">
                                        {{ $comment->user->roles->first()?->name ?? 'User' }}
                                    </span>
                                    @if($comment->is_internal)
                                        <span class="text-[10px] px-2 py-0.2 rounded bg-amber-500/20 text-amber-300 font-bold uppercase">Internal Note</span>
                                    @endif
                                </div>
                                <span class="text-[10px] text-slate-500">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-slate-300 whitespace-pre-line leading-relaxed">{{ $comment->comment }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 text-center py-4">Belum ada tanggapan atau catatan pada tiket ini.</p>
                    @endforelse
                </div>

                <!-- Add Comment Form -->
                <form action="{{ route('service-desk.tickets.comment', $ticket) }}" method="POST" class="pt-3 border-t border-slate-800 space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Tulis Tanggapan / Update Progress</label>
                        <textarea name="comment" rows="3" required placeholder="Tuliskan pesan, instruksi, atau perkembangan perbaikan..."
                            class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white text-xs focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="flex items-center justify-between">
                        @hasanyrole('Super Admin|Admin Persandian|IT Technician')
                            <label class="flex items-center gap-2 text-xs text-amber-300 cursor-pointer">
                                <input type="checkbox" name="is_internal" value="1" class="rounded bg-slate-950 border-slate-700 text-amber-500">
                                <span>Catatan Internal Teknisi (Tidak terlihat oleh OPD)</span>
                            </label>
                        @else
                            <div></div>
                        @endhasanyrole

                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold transition cursor-pointer">
                            Kirim Komentar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Col 3: Ticket Meta, Assigned Tech & Status Update -->
        <div class="space-y-6">
            <!-- Metadata Info Card -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 text-xs space-y-3">
                <h3 class="font-bold text-white text-sm pb-2 border-b border-slate-800">Detail Tiket</h3>

                <div>
                    <span class="text-slate-500 block text-[10px]">Tingkat Prioritas</span>
                    <span class="font-bold uppercase text-xs
                        @if($ticket->priority === 'critical') text-rose-400
                        @elseif($ticket->priority === 'high') text-orange-400
                        @elseif($ticket->priority === 'medium') text-yellow-400
                        @else text-slate-300 @endif">
                        {{ $ticket->priority }}
                    </span>
                </div>

                <div>
                    <span class="text-slate-500 block text-[10px]">Kategori Layanan</span>
                    <span class="text-slate-200 font-medium">{{ ucwords(str_replace('_', ' ', $ticket->category)) }}</span>
                </div>

                <div>
                    <span class="text-slate-500 block text-[10px]">Katalog Layanan IT</span>
                    <span class="text-slate-200">{{ $ticket->service?->name ?? 'Layanan Umum' }}</span>
                </div>

                <div>
                    <span class="text-slate-500 block text-[10px]">Batas Waktu SLA</span>
                    <span class="font-mono text-slate-200">{{ $ticket->sla_due_at ? $ticket->sla_due_at->format('d M Y H:i') : '-' }}</span>
                </div>

                <!-- Related CI Connection -->
                @if($ticket->configurationItem)
                    <div class="pt-2 border-t border-slate-800">
                        <span class="text-[10px] text-blue-400 font-bold block mb-1">Configuration Item (CMDB)</span>
                        <a href="{{ route('cmdb.show', $ticket->configurationItem) }}" class="p-2 rounded-lg bg-slate-950/80 border border-slate-800 block hover:border-blue-500 transition">
                            <span class="font-mono text-blue-400 font-bold block">{{ $ticket->configurationItem->ci_code }}</span>
                            <span class="text-white">{{ $ticket->configurationItem->name }}</span>
                        </a>
                    </div>
                @endif
            </div>

            <!-- Technician & Status Controls (for IT Staff & Admin) -->
            @hasanyrole('Super Admin|Admin Persandian|IT Technician')
                <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 text-xs">
                    <h3 class="font-bold text-white text-sm mb-3 pb-2 border-b border-slate-800">Kelola Status & Penugasan</h3>

                    <form action="{{ route('service-desk.tickets.status', $ticket) }}" method="POST" class="space-y-3">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="block font-medium text-slate-300 mb-1">Tugaskan Teknisi</label>
                            <select name="assigned_technician_id" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700 rounded-xl text-white">
                                <option value="">-- Belum Ditugaskan --</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}" {{ $ticket->assigned_technician_id == $tech->id ? 'selected' : '' }}>
                                        {{ $tech->name }} ({{ $tech->roles->first()?->name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-medium text-slate-300 mb-1">Update Status Tiket</label>
                            <select name="status" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700 rounded-xl text-white">
                                <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Open</option>
                                <option value="assigned" {{ $ticket->status == 'assigned' ? 'selected' : '' }}>Assigned</option>
                                <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="waiting" {{ $ticket->status == 'waiting' ? 'selected' : '' }}>Waiting</option>
                                <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-medium text-slate-300 mb-1">Catatan Penyelesaian (Jika Selesai)</label>
                            <textarea name="resolution_notes" rows="3" placeholder="Jelaskan tindakan yang telah dilakukan untuk menyelesaikan tiket..."
                                class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700 rounded-xl text-white">{{ $ticket->resolution_notes }}</textarea>
                        </div>

                        <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-medium transition cursor-pointer">
                            Simpan Perubahan
                        </button>
                    </form>
                </div>
            @endhasanyrole
        </div>
    </div>
</div>
@endsection
