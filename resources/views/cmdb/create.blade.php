@extends('layouts.app')

@section('title', 'Daftarkan Configuration Item Baru')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('cmdb.index') }}" class="hover:underline">CMDB</a>
                <span>/</span>
                <span class="text-white">Tambah CI</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Daftarkan Configuration Item Baru</h1>
            <p class="text-xs text-slate-400 mt-0.5">Entri data konfigurasi teknis ke dalam repository CMDB terintegrasi</p>
        </div>
        <a href="{{ route('cmdb.index') }}" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium transition">
            &larr; Kembali
        </a>
    </div>

    <form action="{{ route('cmdb.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Section 1: General & Classification -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 sm:p-6 space-y-4">
            <h2 class="text-sm font-bold text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                <span>Klasifikasi & Kepemilikan</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="sm:col-span-2">
                    <label class="block font-medium text-slate-300 mb-1">Nama Configuration Item *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        placeholder="Contoh: Server Web Produksi 01 / Portal Resmi Ciamis"
                        class="w-full px-3.5 py-2.5 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Tipe CI *</label>
                    <select name="ci_type_id" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Tipe CI --</option>
                        @foreach($ciTypes as $type)
                            <option value="{{ $type->id }}" {{ old('ci_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }} ({{ ucfirst($type->category) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Hubungkan ke Fisik IT Asset (ITAM)</label>
                    <select name="asset_id" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Opsional: Pilih Aset Fisik --</option>
                        @foreach($assets as $asset)
                            <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                                [{{ $asset->asset_number }}] {{ $asset->name }} ({{ $asset->brand }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Organisasi / OPD Pemilik *</label>
                    <select name="organization_id" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih OPD --</option>
                        @foreach($organizations as $org)
                            <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>
                                {{ $org->name }} ({{ $org->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Lokasi Fisik Ruang</label>
                    <select name="location_id" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Lokasi --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }} @if($loc->room) - {{ $loc->room }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Unit / Bidang Penanggung Jawab</label>
                    <input type="text" name="responsible_unit" value="{{ old('responsible_unit') }}" placeholder="Contoh: Bidang Sandi & Siber"
                        class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Personil Kontak / PIC</label>
                    <input type="text" name="owner_person" value="{{ old('owner_person') }}" placeholder="Contoh: Budi Santoso (Admin Server)"
                        class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                </div>
            </div>
        </div>

        <!-- Section 2: Technical & Network Specs -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 sm:p-6 space-y-4">
            <h2 class="text-sm font-bold text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                <span>Parameter Jaringan & Teknis</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Hostname</label>
                    <input type="text" name="hostname" value="{{ old('hostname') }}" placeholder="srv-web01.ciamiskab.go.id"
                        class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white font-mono">
                </div>
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Alamat IP (IPv4 / IPv6)</label>
                    <input type="text" name="ip_address" value="{{ old('ip_address') }}" placeholder="192.168.10.20 atau 103.147.220.x"
                        class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white font-mono">
                </div>
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Alamat MAC</label>
                    <input type="text" name="mac_address" value="{{ old('mac_address') }}" placeholder="00:1A:2B:3C:4D:5E"
                        class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white font-mono">
                </div>
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Domain Name (FQDN)</label>
                    <input type="text" name="domain" value="{{ old('domain') }}" placeholder="ciamiskab.go.id"
                        class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                </div>
                <div class="sm:col-span-2">
                    <label class="block font-medium text-slate-300 mb-1">URL Endpoint / Web Address</label>
                    <input type="url" name="url" value="{{ old('url') }}" placeholder="https://ciamiskab.go.id"
                        class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                </div>
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Sistem Operasi (OS)</label>
                    <input type="text" name="operating_system" value="{{ old('operating_system') }}" placeholder="Ubuntu Linux / FortiOS / Windows Server"
                        class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                </div>
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Versi OS / Firmware</label>
                    <input type="text" name="os_version" value="{{ old('os_version') }}" placeholder="24.04 LTS / 7.4.3"
                        class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                </div>
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Nomor Seri Fisik</label>
                    <input type="text" name="serial_number" value="{{ old('serial_number') }}" placeholder="SN-XXXX-XXXX"
                        class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white font-mono">
                </div>
            </div>
        </div>

        <!-- Section 3: Operational Status & Criticality -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 sm:p-6 space-y-4">
            <h2 class="text-sm font-bold text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Status Operasional & Kritikalitas</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Environment *</label>
                    <select name="environment" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:ring-2 focus:ring-blue-500">
                        <option value="production" {{ old('environment', 'production') == 'production' ? 'selected' : '' }}>Production (Live)</option>
                        <option value="staging" {{ old('environment') == 'staging' ? 'selected' : '' }}>Staging / UAT</option>
                        <option value="development" {{ old('environment') == 'development' ? 'selected' : '' }}>Development</option>
                        <option value="dr" {{ old('environment') == 'dr' ? 'selected' : '' }}>Disaster Recovery (DR)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Status Operasional *</label>
                    <select name="status" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:ring-2 focus:ring-blue-500">
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active (Aktif)</option>
                        <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance (Pemeliharaan)</option>
                        <option value="warning" {{ old('status') == 'warning' ? 'selected' : '' }}>Warning (Peringatan)</option>
                        <option value="down" {{ old('status') == 'down' ? 'selected' : '' }}>Down (Mati)</option>
                        <option value="planned" {{ old('status') == 'planned' ? 'selected' : '' }}>Planned (Rencana)</option>
                        <option value="retired" {{ old('status') == 'retired' ? 'selected' : '' }}>Retired (Purna Tugas)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Tingkat Kritikalitas *</label>
                    <select name="criticality" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:ring-2 focus:ring-blue-500">
                        <option value="critical" {{ old('criticality') == 'critical' ? 'selected' : '' }}>Critical (Kritis)</option>
                        <option value="high" {{ old('criticality') == 'high' ? 'selected' : '' }}>High (Tinggi)</option>
                        <option value="medium" {{ old('criticality', 'medium') == 'medium' ? 'selected' : '' }}>Medium (Sedang)</option>
                        <option value="low" {{ old('criticality') == 'low' ? 'selected' : '' }}>Low (Rendah)</option>
                    </select>
                </div>

                <div class="sm:col-span-3">
                    <label class="block font-medium text-slate-300 mb-1">Deskripsi & Catatan Konfigurasi</label>
                    <textarea name="description" rows="3" placeholder="Jelaskan peran sistem, konfigurasi khusus, atau catatan persandian..."
                        class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('cmdb.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold transition shadow-lg shadow-blue-600/30 cursor-pointer">
                Simpan Configuration Item
            </button>
        </div>
    </form>
</div>
@endsection
