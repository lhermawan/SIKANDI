@extends('layouts.app')

@section('title', 'Edit CI: ' . $cmdb->ci_code)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('cmdb.index') }}" class="hover:underline">CMDB</a>
                <span>/</span>
                <a href="{{ route('cmdb.show', $cmdb) }}" class="hover:underline">{{ $cmdb->ci_code }}</a>
                <span>/</span>
                <span class="text-white">Edit</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Edit Configuration Item: {{ $cmdb->ci_code }}</h1>
            <p class="text-xs text-slate-400 mt-0.5">Perbarui spesifikasi dan parameter CI di repository</p>
        </div>
        <a href="{{ route('cmdb.show', $cmdb) }}" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium transition">
            &larr; Kembali ke Detail
        </a>
    </div>

    <form action="{{ route('cmdb.update', $cmdb) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Section 1: General & Classification -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 sm:p-6 space-y-4">
            <h2 class="text-sm font-bold text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                <span>Klasifikasi & Kepemilikan</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="sm:col-span-2">
                    <label class="block font-medium text-slate-300 mb-1">Nama Configuration Item *</label>
                    <input type="text" name="name" value="{{ old('name', $cmdb->name) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Tipe CI *</label>
                    <select name="ci_type_id" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        @foreach($ciTypes as $type)
                            <option value="{{ $type->id }}" {{ old('ci_type_id', $cmdb->ci_type_id) == $type->id ? 'selected' : '' }}>
                                {{ $type->name }} ({{ ucfirst($type->category) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Hubungkan ke IT Asset (ITAM)</label>
                    <select name="asset_id" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        <option value="">-- Tidak Terhubung / Virtual --</option>
                        @foreach($assets as $asset)
                            <option value="{{ $asset->id }}" {{ old('asset_id', $cmdb->asset_id) == $asset->id ? 'selected' : '' }}>
                                [{{ $asset->asset_number }}] {{ $asset->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Organisasi / OPD Pemilik *</label>
                    <select name="organization_id" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        @foreach($organizations as $org)
                            <option value="{{ $org->id }}" {{ old('organization_id', $cmdb->organization_id) == $org->id ? 'selected' : '' }}>
                                {{ $org->name }} ({{ $org->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Lokasi Fisik Ruang</label>
                    <select name="location_id" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        <option value="">-- Pilih Lokasi --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('location_id', $cmdb->location_id) == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }} @if($loc->room) - {{ $loc->room }} @endif
                            </option>
                        @endforeach
                    </select>
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
                    <input type="text" name="hostname" value="{{ old('hostname', $cmdb->hostname) }}" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white font-mono">
                </div>
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Alamat IP</label>
                    <input type="text" name="ip_address" value="{{ old('ip_address', $cmdb->ip_address) }}" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white font-mono">
                </div>
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Alamat MAC</label>
                    <input type="text" name="mac_address" value="{{ old('mac_address', $cmdb->mac_address) }}" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white font-mono">
                </div>
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Domain Name (FQDN)</label>
                    <input type="text" name="domain" value="{{ old('domain', $cmdb->domain) }}" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                </div>
                <div class="sm:col-span-2">
                    <label class="block font-medium text-slate-300 mb-1">URL Endpoint / Website</label>
                    <input type="url" name="url" value="{{ old('url', $cmdb->url) }}" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                </div>
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Sistem Operasi (OS)</label>
                    <input type="text" name="operating_system" value="{{ old('operating_system', $cmdb->operating_system) }}" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                </div>
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Versi OS / Firmware</label>
                    <input type="text" name="os_version" value="{{ old('os_version', $cmdb->os_version) }}" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                </div>
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Nomor Seri Fisik</label>
                    <input type="text" name="serial_number" value="{{ old('serial_number', $cmdb->serial_number) }}" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white font-mono">
                </div>
            </div>
        </div>

        <!-- Section 3: Status & Criticality -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 sm:p-6 space-y-4">
            <h2 class="text-sm font-bold text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Status Operasional & Kritikalitas</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Environment *</label>
                    <select name="environment" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        <option value="production" {{ old('environment', $cmdb->environment) == 'production' ? 'selected' : '' }}>Production</option>
                        <option value="staging" {{ old('environment', $cmdb->environment) == 'staging' ? 'selected' : '' }}>Staging</option>
                        <option value="development" {{ old('environment', $cmdb->environment) == 'development' ? 'selected' : '' }}>Development</option>
                        <option value="dr" {{ old('environment', $cmdb->environment) == 'dr' ? 'selected' : '' }}>Disaster Recovery (DR)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Status Operasional *</label>
                    <select name="status" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        <option value="active" {{ old('status', $cmdb->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="maintenance" {{ old('status', $cmdb->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="warning" {{ old('status', $cmdb->status) == 'warning' ? 'selected' : '' }}>Warning</option>
                        <option value="down" {{ old('status', $cmdb->status) == 'down' ? 'selected' : '' }}>Down</option>
                        <option value="planned" {{ old('status', $cmdb->status) == 'planned' ? 'selected' : '' }}>Planned</option>
                        <option value="retired" {{ old('status', $cmdb->status) == 'retired' ? 'selected' : '' }}>Retired</option>
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Tingkat Kritikalitas *</label>
                    <select name="criticality" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        <option value="critical" {{ old('criticality', $cmdb->criticality) == 'critical' ? 'selected' : '' }}>Critical</option>
                        <option value="high" {{ old('criticality', $cmdb->criticality) == 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ old('criticality', $cmdb->criticality) == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ old('criticality', $cmdb->criticality) == 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>

                <div class="sm:col-span-3">
                    <label class="block font-medium text-slate-300 mb-1">Deskripsi</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">{{ old('description', $cmdb->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between pt-2">
            <button type="button" onclick="if(confirm('Apakah Anda yakin ingin menghapus CI ini?')) document.getElementById('deleteCiForm').submit();" class="px-4 py-2 bg-rose-950/60 hover:bg-rose-900 border border-rose-800 text-rose-300 rounded-xl text-xs font-semibold transition cursor-pointer">
                Hapus CI Ini
            </button>
            <div class="flex items-center gap-3">
                <a href="{{ route('cmdb.show', $cmdb) }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold transition shadow-lg shadow-blue-600/30 cursor-pointer">
                    Perbarui Configuration Item
                </button>
            </div>
        </div>
    </form>

    <form id="deleteCiForm" action="{{ route('cmdb.destroy', $cmdb) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
