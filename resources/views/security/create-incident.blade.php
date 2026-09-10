@extends('layouts.app')

@section('title', 'Lapor Insiden Siber (CSIRT)')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('security.incidents') }}" class="hover:underline">CSIRT</a>
                <span>/</span>
                <span class="text-white">Lapor Insiden</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Laporan Insiden Keamanan Informasi (CSIRT)</h1>
            <p class="text-xs text-slate-400 mt-0.5">Sampaikan laporan darurat siber untuk segera ditangani oleh Tim Tanggap Insiden Diskominfo Ciamis</p>
        </div>
        <a href="{{ route('security.incidents') }}" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium transition">
            &larr; Kembali
        </a>
    </div>

    <form action="{{ route('security.incidents.store') }}" method="POST" class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-6 space-y-4 text-xs">
        @csrf

        <div>
            <label class="block font-medium text-slate-300 mb-1">Judul Insiden Siber *</label>
            <input type="text" name="title" value="{{ old('title') }}" required placeholder="Contoh: Terdeteksi Tampilan Defacement Situs Resmi / Serangan Ransomware"
                class="w-full px-3.5 py-2.5 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-medium text-slate-300 mb-1">Tipe Insiden Siber *</label>
                <select name="incident_type" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                    <option value="defacement">Web Defacement (Perubahan Tampilan)</option>
                    <option value="malware">Malware / Ransomware</option>
                    <option value="phishing">Phishing / Social Engineering</option>
                    <option value="unauthorized_access">Akses Tidak Sah / Unauthorized Access</option>
                    <option value="account_compromise">Peretasan Akun / Compromised Credentials</option>
                    <option value="data_exposure">Kebocoran Data (Data Leak)</option>
                    <option value="vulnerability">Kerentanan Kritis (0-Day / CVE)</option>
                    <option value="website_attack">Serangan DDoS / Web Injection</option>
                    <option value="network_attack">Serangan Jaringan / Port Scan</option>
                    <option value="other">Lainnya</option>
                </select>
            </div>

            <div>
                <label class="block font-medium text-slate-300 mb-1">Tingkat Keparahan (Severity) *</label>
                <select name="severity" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                    <option value="critical">Critical (Layanan Publik Lumpuh / Data Sensitif Bocor)</option>
                    <option value="high">High (Kerusakan Signifikan)</option>
                    <option value="medium">Medium (Gangguan Terkendali)</option>
                    <option value="low">Low (Upaya Serangan Gagal)</option>
                </select>
            </div>

            <div>
                <label class="block font-medium text-slate-300 mb-1">OPD Terdampak *</label>
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
        </div>

        <div>
            <label class="block font-medium text-slate-300 mb-1">Deskripsi Lengkap Insiden *</label>
            <textarea name="description" rows="4" required placeholder="Jelaskan waktu kejadian, indikasi serangan, dan kronologi singkat..."
                class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white"></textarea>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
            <a href="{{ route('security.incidents') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl">Batal</a>
            <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl font-medium cursor-pointer shadow-lg shadow-red-600/30">
                Kirim Laporan CSIRT
            </button>
        </div>
    </form>
</div>
@endsection
