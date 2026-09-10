@extends('layouts.app')

@section('title', 'Edit Aset: ' . $asset->asset_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('itam.index') }}" class="hover:underline">IT Asset</a>
                <span>/</span>
                <a href="{{ route('itam.show', $asset) }}" class="hover:underline">{{ $asset->asset_number }}</a>
                <span>/</span>
                <span class="text-white">Edit</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Edit Aset: {{ $asset->asset_number }}</h1>
            <p class="text-xs text-slate-400 mt-0.5">Perbarui data inventaris fisik dan penugasan aset</p>
        </div>
        <a href="{{ route('itam.show', $asset) }}" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium transition">
            &larr; Kembali
        </a>
    </div>

    <form action="{{ route('itam.update', $asset) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 sm:p-6 space-y-4">
            <h2 class="text-sm font-bold text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <span>Data Pokok Aset</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="sm:col-span-2">
                    <label class="block font-medium text-slate-300 mb-1">Nama Aset TIK *</label>
                    <input type="text" name="name" value="{{ old('name', $asset->name) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Kategori Aset *</label>
                    <select name="asset_category_id" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('asset_category_id', $asset->asset_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">OPD Pemegang Aset *</label>
                    <select name="organization_id" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        @foreach($organizations as $org)
                            <option value="{{ $org->id }}" {{ old('organization_id', $asset->organization_id) == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Merk / Brand</label>
                    <input type="text" name="brand" value="{{ old('brand', $asset->brand) }}" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Model / Tipe</label>
                    <input type="text" name="model" value="{{ old('model', $asset->model) }}" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Nomor Seri Fisik</label>
                    <input type="text" name="serial_number" value="{{ old('serial_number', $asset->serial_number) }}" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white font-mono">
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Lokasi Ruang</label>
                    <select name="location_id" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        <option value="">-- Pilih Lokasi --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('location_id', $asset->location_id) == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Pegawai Pemegang</label>
                    <select name="assigned_to_user_id" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        <option value="">-- Belum Ditugaskan --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ old('assigned_to_user_id', $asset->assigned_to_user_id) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 sm:p-6 space-y-4">
            <h2 class="text-sm font-bold text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                <span>Siklus Hidup & Kondisi</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Status Siklus Hidup *</label>
                    <select name="lifecycle_status" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        <option value="in_use" {{ old('lifecycle_status', $asset->lifecycle_status) == 'in_use' ? 'selected' : '' }}>In Use</option>
                        <option value="inventory" {{ old('lifecycle_status', $asset->lifecycle_status) == 'inventory' ? 'selected' : '' }}>Inventory</option>
                        <option value="maintenance" {{ old('lifecycle_status', $asset->lifecycle_status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="repair" {{ old('lifecycle_status', $asset->lifecycle_status) == 'repair' ? 'selected' : '' }}>Repair</option>
                        <option value="disposed" {{ old('lifecycle_status', $asset->lifecycle_status) == 'disposed' ? 'selected' : '' }}>Disposed</option>
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Kondisi Fisik *</label>
                    <select name="condition" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        <option value="good" {{ old('condition', $asset->condition) == 'good' ? 'selected' : '' }}>Baik</option>
                        <option value="light_damage" {{ old('condition', $asset->condition) == 'light_damage' ? 'selected' : '' }}>Rusak Ringan</option>
                        <option value="heavy_damage" {{ old('condition', $asset->condition) == 'heavy_damage' ? 'selected' : '' }}>Rusak Berat</option>
                        <option value="lost" {{ old('condition', $asset->condition) == 'lost' ? 'selected' : '' }}>Hilang</option>
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Garansi Berakhir</label>
                    <input type="date" name="warranty_expiry_date" value="{{ old('warranty_expiry_date', $asset->warranty_expiry_date?->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                </div>

                <div class="sm:col-span-3">
                    <label class="block font-medium text-slate-300 mb-1">Catatan</label>
                    <textarea name="notes" rows="3" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">{{ old('notes', $asset->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('itam.show', $asset) }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold transition shadow-lg shadow-blue-600/30 cursor-pointer">
                Perbarui Data Aset
            </button>
        </div>
    </form>
</div>
@endsection
