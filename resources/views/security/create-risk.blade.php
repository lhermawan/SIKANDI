@extends('layouts.app')

@section('title', 'Tambah Register Risiko')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('security.risks') }}" class="hover:underline">Manajemen Risiko</a>
                <span>/</span>
                <span class="text-white">Tambah Risiko</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Daftarkan Risiko Keamanan Informasi</h1>
            <p class="text-xs text-slate-400 mt-0.5">Penilaian probabilitas (likelihood) dan dampak (impact) terhadap aset / CI</p>
        </div>
        <a href="{{ route('security.risks') }}" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium transition">
            &larr; Kembali
        </a>
    </div>

    <form action="{{ route('security.risks.store') }}" method="POST" class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-6 space-y-4 text-xs">
        @csrf

        <div>
            <label class="block font-medium text-slate-300 mb-1">Nama / Judul Risiko *</label>
            <input type="text" name="title" value="{{ old('title') }}" required placeholder="Contoh: Ancaman Ransomware pada Server Database SIMRS"
                class="w-full px-3.5 py-2.5 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-medium text-slate-300 mb-1">OPD Pemilik Risiko *</label>
                <select name="organization_id" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                    @foreach($organizations as $org)
                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-medium text-slate-300 mb-1">Kaitkan ke CI Terdampak (CMDB)</label>
                <select name="ci_id" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                    <option value="">-- Opsional: Pilih CI --</option>
                    @foreach($cis as $ci)
                        <option value="{{ $ci->id }}">[{{ $ci->ci_code }}] {{ $ci->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-medium text-slate-300 mb-1">Ancaman (Threat)</label>
                <input type="text" name="threat" placeholder="Contoh: Eksploitasi port RDP terbuka / phising pegawai"
                    class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
            </div>

            <div>
                <label class="block font-medium text-slate-300 mb-1">Kerentanan (Vulnerability)</label>
                <input type="text" name="vulnerability" placeholder="Contoh: Patch keamanan belum diupdate / password lemah"
                    class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
            </div>

            <div>
                <label class="block font-medium text-slate-300 mb-1">Kemungkinan Kejadian (Likelihood 1-5) *</label>
                <select name="likelihood" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                    <option value="1">1 - Sangat Jarang (Rare)</option>
                    <option value="2">2 - Jarang (Unlikely)</option>
                    <option value="3" selected>3 - Sedang (Possible)</option>
                    <option value="4">4 - Sering (Likely)</option>
                    <option value="5">5 - Hampir Pasti (Almost Certain)</option>
                </select>
            </div>

            <div>
                <label class="block font-medium text-slate-300 mb-1">Dampak Kerusakan (Impact 1-5) *</label>
                <select name="impact" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                    <option value="1">1 - Tidak Signifikan (Insignificant)</option>
                    <option value="2">2 - Minor (Kecil)</option>
                    <option value="3" selected>3 - Moderat (Sedang)</option>
                    <option value="4">4 - Mayor (Besar)</option>
                    <option value="5">5 - Bencana / Kritis (Catastrophic)</option>
                </select>
            </div>

            <div>
                <label class="block font-medium text-slate-300 mb-1">Personil Pemilik Risiko (Owner)</label>
                <select name="owner_id" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                    <option value="">-- Pilih Penanggung Jawab --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-medium text-slate-300 mb-1">Batas Waktu Penanganan (Due Date)</label>
                <input type="date" name="due_date" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
            </div>
        </div>

        <div>
            <label class="block font-medium text-slate-300 mb-1">Uraian Risiko</label>
            <textarea name="description" rows="3" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white"></textarea>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
            <a href="{{ route('security.risks') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl">Batal</a>
            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-medium cursor-pointer">
                Simpan ke Register Risiko
            </button>
        </div>
    </form>
</div>
@endsection
