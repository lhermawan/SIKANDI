@extends('layouts.app')

@section('title', 'Insiden: ' . $incident->incident_number)

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('incidents.index') }}" class="hover:underline">Insiden</a>
                <span>/</span>
                <span class="text-white font-mono">{{ $incident->incident_number }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-white tracking-tight">{{ $incident->title }}</h1>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase
                    @if($incident->status === 'open') bg-rose-500/20 text-rose-300 border border-rose-500/30
                    @elseif($incident->status === 'investigation') bg-amber-500/20 text-amber-300 border border-amber-500/30
                    @elseif($incident->status === 'in_progress') bg-indigo-500/20 text-indigo-300 border border-indigo-500/30
                    @elseif($incident->status === 'resolved') bg-emerald-500/20 text-emerald-300 border border-emerald-500/30
                    @else bg-slate-700 text-slate-300 @endif">
                    {{ $incident->status }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">
                Sumber: <span class="text-white font-semibold">{{ ucwords(str_replace('_', ' ', $incident->source)) }}</span> &bull;
                Terdeteksi: <span>{{ $incident->detected_at->format('d M Y H:i') }}</span>
            </p>
        </div>
        <a href="{{ route('incidents.index') }}" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium transition">
            &larr; Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Col 1 & 2: Investigation & Root Cause -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 space-y-4 text-xs">
                <div>
                    <span class="text-slate-500 font-bold uppercase tracking-wider block mb-1">Dampak Operasional (Impact Description)</span>
                    <p class="text-slate-200 bg-slate-950/60 p-3 rounded-xl border border-slate-800 leading-relaxed">{{ $incident->impact_description ?? 'Tidak ada catatan dampak.' }}</p>
                </div>

                <div>
                    <span class="text-slate-500 font-bold uppercase tracking-wider block mb-1">Akar Masalah (Root Cause)</span>
                    <p class="text-slate-200 bg-slate-950/60 p-3 rounded-xl border border-slate-800 leading-relaxed">{{ $incident->root_cause ?? 'Masih dalam proses investigasi teknisi.' }}</p>
                </div>

                @if($incident->resolution)
                    <div>
                        <span class="text-emerald-400 font-bold uppercase tracking-wider block mb-1">Solusi & Tindakan Perbaikan (Resolution)</span>
                        <p class="text-emerald-200 bg-emerald-950/30 p-3 rounded-xl border border-emerald-500/30 leading-relaxed">{{ $incident->resolution }}</p>
                    </div>
                @endif
            </div>

            <!-- Investigation Timeline Comments -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 space-y-4">
                <h2 class="text-sm font-bold text-white">Catatan Investigasi & Tindak Lanjut</h2>

                <div class="space-y-3">
                    @forelse($incident->comments as $c)
                        <div class="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800 text-xs">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-white">{{ $c->user->name }}</span>
                                <span class="text-slate-500 text-[10px]">{{ $c->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-slate-300 leading-relaxed">{{ $c->comment }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 text-center py-3">Belum ada catatan investigasi.</p>
                    @endforelse
                </div>

                <form action="{{ route('incidents.comment', $incident) }}" method="POST" class="pt-3 border-t border-slate-800 space-y-3">
                    @csrf
                    <div>
                        <textarea name="comment" rows="2" required placeholder="Tambahkan temuan teknis atau langkah penanganan..."
                            class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700 rounded-xl text-white text-xs"></textarea>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold cursor-pointer">
                        Simpan Catatan
                    </button>
                </form>
            </div>
        </div>

        <!-- Col 3: Controls & CI Link -->
        <div class="space-y-6">
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 text-xs space-y-3">
                <h3 class="font-bold text-white text-sm pb-2 border-b border-slate-800">Komponen Terkait</h3>

                @if($incident->configurationItem)
                    <div>
                        <span class="text-slate-500 block text-[10px]">Configuration Item (CMDB)</span>
                        <a href="{{ route('cmdb.show', $incident->configurationItem) }}" class="p-2 rounded-lg bg-slate-950/80 border border-slate-800 block hover:border-blue-500 mt-1">
                            <span class="font-mono text-blue-400 font-bold block">{{ $incident->configurationItem->ci_code }}</span>
                            <span class="text-white">{{ $incident->configurationItem->name }}</span>
                        </a>
                    </div>
                @endif

                @if($incident->ticket)
                    <div>
                        <span class="text-slate-500 block text-[10px]">Tiket Service Desk Asal</span>
                        <a href="{{ route('service-desk.tickets.show', $incident->ticket) }}" class="font-mono text-blue-400 hover:underline">
                            {{ $incident->ticket->ticket_number }}
                        </a>
                    </div>
                @endif
            </div>

            <!-- Technician Controls -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 text-xs">
                <h3 class="font-bold text-white text-sm mb-3 pb-2 border-b border-slate-800">Perbarui Status Insiden</h3>

                <form action="{{ route('incidents.update', $incident) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Status Insiden</label>
                        <select name="status" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700 rounded-xl text-white">
                            <option value="open" {{ $incident->status == 'open' ? 'selected' : '' }}>Open</option>
                            <option value="investigation" {{ $incident->status == 'investigation' ? 'selected' : '' }}>Investigation</option>
                            <option value="in_progress" {{ $incident->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="resolved" {{ $incident->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ $incident->status == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Prioritas</label>
                        <select name="priority" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700 rounded-xl text-white">
                            <option value="critical" {{ $incident->priority == 'critical' ? 'selected' : '' }}>Critical</option>
                            <option value="high" {{ $incident->priority == 'high' ? 'selected' : '' }}>High</option>
                            <option value="medium" {{ $incident->priority == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="low" {{ $incident->priority == 'low' ? 'selected' : '' }}>Low</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Teknisi Penanggung Jawab</label>
                        <select name="assigned_technician_id" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700 rounded-xl text-white">
                            <option value="">-- Belum Ditugaskan --</option>
                            @foreach($technicians as $t)
                                <option value="{{ $t->id }}" {{ $incident->assigned_technician_id == $t->id ? 'selected' : '' }}>
                                    {{ $t->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Akar Masalah (Root Cause)</label>
                        <textarea name="root_cause" rows="2" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700 rounded-xl text-white">{{ $incident->root_cause }}</textarea>
                    </div>

                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Solusi & Tindakan</label>
                        <textarea name="resolution" rows="2" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700 rounded-xl text-white">{{ $incident->resolution }}</textarea>
                    </div>

                    <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-medium transition cursor-pointer">
                        Simpan Pembaruan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
