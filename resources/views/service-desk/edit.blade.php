@extends('layouts.app')

@section('title', 'Edit Tiket Layanan')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('service-desk.tickets') }}" class="hover:underline">Service Desk</a>
                <span>/</span>
                <a href="{{ route('service-desk.tickets.show', $ticket) }}" class="hover:underline">{{ $ticket->ticket_number }}</a>
                <span>/</span>
                <span class="text-white">Edit Tiket</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Edit Tiket: {{ $ticket->ticket_number }}</h1>
            <p class="text-xs text-slate-400 mt-0.5">Perbarui rincian permohonan atau aduan layanan IT</p>
        </div>
        <a href="{{ route('service-desk.tickets.show', $ticket) }}" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium transition">
            &larr; Kembali
        </a>
    </div>

    <form action="{{ route('service-desk.tickets.update', $ticket) }}" method="POST" class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-6 space-y-4">
        @csrf
        @method('PUT')

        <div class="space-y-4 text-xs">
            <div>
                <label class="block font-medium text-slate-300 mb-1">Judul Ringkas Aduan / Permohonan *</label>
                <input type="text" name="title" value="{{ old('title', $ticket->title) }}" required
                    class="w-full px-3.5 py-2.5 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Kategori Tiket *</label>
                    <select name="category" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        <option value="service_request" {{ old('category', $ticket->category) == 'service_request' ? 'selected' : '' }}>Permohonan Layanan (Service Request)</option>
                        <option value="incident" {{ old('category', $ticket->category) == 'incident' ? 'selected' : '' }}>Gangguan / Insiden (Incident)</option>
                        <option value="access_request" {{ old('category', $ticket->category) == 'access_request' ? 'selected' : '' }}>Permintaan Hak Akses / Akun</option>
                        <option value="maintenance" {{ old('category', $ticket->category) == 'maintenance' ? 'selected' : '' }}>Pemeliharaan Perangkat</option>
                        <option value="question" {{ old('category', $ticket->category) == 'question' ? 'selected' : '' }}>Konsultasi & Pertanyaan</option>
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Tingkat Prioritas *</label>
                    <select name="priority" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        <option value="critical" {{ old('priority', $ticket->priority) == 'critical' ? 'selected' : '' }}>Kritis (Layanan Utama Terhenti)</option>
                        <option value="high" {{ old('priority', $ticket->priority) == 'high' ? 'selected' : '' }}>Tinggi (Operasional Sangat Terganggu)</option>
                        <option value="medium" {{ old('priority', $ticket->priority) == 'medium' ? 'selected' : '' }}>Sedang (Gangguan Parsial)</option>
                        <option value="low" {{ old('priority', $ticket->priority) == 'low' ? 'selected' : '' }}>Rendah (Permintaan Umum)</option>
                    </select>
                </div>
                
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Organisasi / OPD *</label>
                    <select name="organization_id" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        @foreach($organizations as $org)
                            <option value="{{ $org->id }}" {{ old('organization_id', $ticket->organization_id) == $org->id ? 'selected' : '' }}>
                                {{ $org->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Katalog Layanan IT</label>
                    <select name="service_id" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        <option value="">-- Pilih Katalog Layanan (Opsional) --</option>
                        @foreach($services as $srv)
                            <option value="{{ $srv->id }}" {{ old('service_id', $ticket->service_id) == $srv->id ? 'selected' : '' }}>
                                {{ $srv->name }} (SLA: {{ $srv->sla_resolution_hours }} Jam)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Kaitkan ke Komponen CMDB</label>
                    <select name="ci_id" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        <option value="">-- Pilih CI Terdampak (Opsional) --</option>
                        @foreach($cis as $ci)
                            <option value="{{ $ci->id }}" {{ old('ci_id', $ticket->ci_id) == $ci->id ? 'selected' : '' }}>
                                [{{ $ci->ci_code }}] {{ $ci->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Kaitkan ke Aset Fisik (ITAM)</label>
                    <select name="asset_id" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        <option value="">-- Pilih Aset (Opsional) --</option>
                        @foreach($assets as $ast)
                            <option value="{{ $ast->id }}" {{ old('asset_id', $ticket->asset_id) == $ast->id ? 'selected' : '' }}>
                                [{{ $ast->asset_tag }}] {{ $ast->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-medium text-slate-300 mb-1">Rincian Deskripsi Masalah / Permohonan *</label>
                <textarea name="description" rows="5" required
                    class="w-full px-3.5 py-2.5 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $ticket->description) }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
            <a href="{{ route('service-desk.tickets.show', $ticket) }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold transition shadow-lg shadow-blue-600/30 cursor-pointer">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
