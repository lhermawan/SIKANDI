@extends('layouts.app')

@section('title', 'Daftarkan Aset TIK Baru')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('itam.index') }}" class="hover:underline">IT Asset</a>
                <span>/</span>
                <span class="text-white">Tambah Aset</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Daftarkan Aset TIK Baru</h1>
            <p class="text-xs text-slate-400 mt-0.5">Entri inventaris perangkat keras atau perlengkapan TIK Diskominfo / OPD Ciamis</p>
        </div>
        <a href="{{ route('itam.index') }}" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium transition">
            &larr; Kembali
        </a>
    </div>

    <form action="{{ route('itam.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 sm:p-6 space-y-4">
            <h2 class="text-sm font-bold text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <span>Data Pokok Aset</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="sm:col-span-2">
                    <label class="block font-medium text-slate-300 mb-1">Nama Aset TIK *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        placeholder="Contoh: Dell PowerEdge R740 Server / Laptop Lenovo ThinkPad X1"
                        class="w-full px-3.5 py-2.5 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Kategori Aset *</label>
                    <select name="asset_category_id" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('asset_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">OPD Pemegang Aset *</label>
                    <select name="organization_id" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        <option value="">-- Pilih OPD --</option>
                        @foreach($organizations as $org)
                            <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }} ({{ $org->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Merk / Brand</label>
                    <input type="text" name="brand" value="{{ old('brand') }}" placeholder="Dell / Cisco / Lenovo"
                        class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Model / Tipe Produk</label>
                    <input type="text" name="model" value="{{ old('model') }}" placeholder="PowerEdge R740 / Catalyst 3850"
                        class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Nomor Seri Fisik (Serial Number)</label>
                    <input type="text" name="serial_number" value="{{ old('serial_number') }}" placeholder="SN-XXXXX-XXXXX"
                        class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white font-mono">
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Lokasi Ruang / Gedung</label>
                    <select name="location_id" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        <option value="">-- Pilih Lokasi --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }} @if($loc->room) - {{ $loc->room }} @endif</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Pegawai Pemegang (Assigned User)</label>
                    <select name="assigned_to_user_id" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        <option value="">-- Belum Ditugaskan --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ old('assigned_to_user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->nip ?? $u->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Vendor / Rekanan Pengadaan</label>
                    <select name="vendor_id" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        <option value="">-- Pilih Vendor --</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->id }}" {{ old('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 2: Financial & Lifecycle -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 sm:p-6 space-y-4">
            <h2 class="text-sm font-bold text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                <span>Pengadaan, Garansi & Siklus Hidup</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Tanggal Pembelian</label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date') }}"
                        class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                </div>
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Harga Pembelian (Rp)</label>
                    <input type="number" step="0.01" name="purchase_price" value="{{ old('purchase_price') }}" placeholder="15000000"
                        class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                </div>
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Garansi Berakhir</label>
                    <input type="date" name="warranty_expiry_date" value="{{ old('warranty_expiry_date') }}"
                        class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Status Siklus Hidup *</label>
                    <select name="lifecycle_status" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        <option value="in_use" {{ old('lifecycle_status', 'in_use') == 'in_use' ? 'selected' : '' }}>In Use (Sedang Digunakan)</option>
                        <option value="inventory" {{ old('lifecycle_status') == 'inventory' ? 'selected' : '' }}>Inventory (Penyimpanan Gudang)</option>
                        <option value="procurement" {{ old('lifecycle_status') == 'procurement' ? 'selected' : '' }}>Procurement (Pengadaan)</option>
                        <option value="received" {{ old('lifecycle_status') == 'received' ? 'selected' : '' }}>Received (Diterima)</option>
                        <option value="assigned" {{ old('lifecycle_status') == 'assigned' ? 'selected' : '' }}>Assigned (Diserahkan)</option>
                        <option value="maintenance" {{ old('lifecycle_status') == 'maintenance' ? 'selected' : '' }}>Maintenance (Pemeliharaan)</option>
                        <option value="repair" {{ old('lifecycle_status') == 'repair' ? 'selected' : '' }}>Repair (Perbaikan)</option>
                        <option value="returned" {{ old('lifecycle_status') == 'returned' ? 'selected' : '' }}>Returned (Dikembalikan)</option>
                        <option value="disposed" {{ old('lifecycle_status') == 'disposed' ? 'selected' : '' }}>Disposed (Dihapus)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Kondisi Fisik *</label>
                    <select name="condition" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                        <option value="good" {{ old('condition', 'good') == 'good' ? 'selected' : '' }}>Baik (Good)</option>
                        <option value="light_damage" {{ old('condition') == 'light_damage' ? 'selected' : '' }}>Rusak Ringan (Light Damage)</option>
                        <option value="heavy_damage" {{ old('condition') == 'heavy_damage' ? 'selected' : '' }}>Rusak Berat (Heavy Damage)</option>
                        <option value="lost" {{ old('condition') == 'lost' ? 'selected' : '' }}>Hilang (Lost)</option>
                    </select>
                </div>

                <div class="sm:col-span-3">
                    <label class="block font-medium text-slate-300 mb-1">Catatan Tambahan</label>
                    <textarea name="notes" rows="3" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('itam.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold transition shadow-lg shadow-blue-600/30 cursor-pointer">
                Simpan & Buat QR Code Aset
            </button>
        </div>
    </form>
</div>
@endsection
