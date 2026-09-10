@extends('layouts.app')

@section('title', 'Buat Laporan Insiden Baru')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('incidents.index') }}" class="hover:underline">Insiden</a>
                <span>/</span>
                <span class="text-white">Buat Insiden</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Daftarkan Laporan Insiden Baru</h1>
            <p class="text-xs text-slate-400 mt-0.5">Catat gangguan operasional atau eskalasi sistem dengan menghubungkan ke CI dan Aset terkait</p>
        </div>
        <a href="{{ route('incidents.index') }}" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium transition cursor-pointer">
            &larr; Kembali
        </a>
    </div>

    <form action="{{ route('incidents.store') }}" method="POST" class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-6 space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Judul Insiden *</label>
            <input type="text" name="title" required value="{{ old('title') }}" placeholder="e.g. Downtime Aplikasi SIMPATIK Akibat Kegagalan Database"
                class="w-full px-3.5 py-2.5 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white text-xs placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Sumber Deteksi *</label>
                <select name="source" required class="w-full px-3.5 py-2.5 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="manual_report">Laporan Manual</option>
                    <option value="service_desk">Service Desk Ticket</option>
                    <option value="monitoring">Automated Monitoring</option>
                    <option value="security_monitoring">CSIRT / Security</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Prioritas / Urgensi *</label>
                <select name="priority" required class="w-full px-3.5 py-2.5 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="low">Low (Rendah)</option>
                    <option value="medium" selected>Medium (Sedang)</option>
                    <option value="high">High (Tinggi)</option>
                    <option value="critical">Critical (Kritis)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Status Awal *</label>
                <select name="status" required class="w-full px-3.5 py-2.5 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="open" selected>Open (Baru)</option>
                    <option value="investigation">Investigation</option>
                    <option value="in_progress">In Progress</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">OPD Terdampak</label>
                <select name="organization_id" class="w-full px-3.5 py-2.5 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih OPD --</option>
                    @foreach($organizations as $org)
                        <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->code }} - {{ $org->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Configuration Item (CMDB)</label>
                <select name="ci_id" class="w-full px-3.5 py-2.5 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Tidak Terkait CI Langsung --</option>
                    @foreach($cis as $ci)
                        <option value="{{ $ci->id }}" {{ old('ci_id') == $ci->id ? 'selected' : '' }}>[{{ $ci->ci_code }}] {{ $ci->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Aset Fisik (ITAM)</label>
                <select name="asset_id" class="w-full px-3.5 py-2.5 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Tidak Terkait Aset --</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>[{{ $asset->asset_number }}] {{ $asset->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Deskripsi Dampak & Gangguan</label>
            <textarea name="impact_description" rows="3" placeholder="Jelaskan dampak terhadap layanan publik atau kinerja pemerintahan..."
                class="w-full px-3.5 py-2.5 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white text-xs placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('impact_description') }}</textarea>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Dugaan / Analisis Akar Masalah Awal (Root Cause)</label>
            <textarea name="root_cause" rows="2" placeholder="Jika sudah ada indikasi penyebab awal..."
                class="w-full px-3.5 py-2.5 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white text-xs placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('root_cause') }}</textarea>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
            <a href="{{ route('incidents.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition cursor-pointer">Batal</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold shadow-lg shadow-rose-600/30 transition cursor-pointer">
                Daftarkan Insiden
            </button>
        </div>
    </form>
</div>
@endsection
