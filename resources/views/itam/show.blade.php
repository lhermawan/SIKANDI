@extends('layouts.app')

@section('title', 'Aset: ' . $asset->asset_number . ' — ' . $asset->name)

@section('content')
<div class="space-y-6">
    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('itam.index') }}" class="hover:underline">IT Asset</a>
                <span>/</span>
                <span class="text-white">{{ $asset->asset_number }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-white tracking-tight">{{ $asset->name }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase
                    @if($asset->lifecycle_status === 'in_use') bg-emerald-500/20 text-emerald-300 border border-emerald-500/30
                    @elseif($asset->lifecycle_status === 'maintenance') bg-indigo-500/20 text-indigo-300 border border-indigo-500/30
                    @elseif($asset->lifecycle_status === 'repair') bg-amber-500/20 text-amber-300 border border-amber-500/30
                    @else bg-slate-700 text-slate-300 @endif">
                    {{ $asset->lifecycle_status }}
                </span>
                <span class="text-xs font-semibold px-2 py-0.5 rounded bg-slate-800 text-slate-300">
                    Kondisi: {{ strtoupper($asset->condition) }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">
                Kategori: <span class="text-slate-200">{{ $asset->category->name }}</span> &bull;
                OPD: <span class="text-slate-200">{{ $asset->organization->name }}</span>
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('itam.print-label', $asset) }}" target="_blank" class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-lg shadow-amber-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Label QR Barcode</span>
            </a>
            <a href="{{ route('itam.edit', $asset) }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold transition border border-slate-700">
                Edit Aset
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Col 1 & 2: Technical Specs, CMDB Link, Maintenance -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Asset Details Card -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5">
                <h2 class="font-bold text-white text-sm mb-4 pb-2 border-b border-slate-800 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    <span>Informasi Pengadaan & Spesifikasi Fisik</span>
                </h2>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-4 gap-x-6 text-xs">
                    <div>
                        <span class="text-slate-500 block text-[11px]">Nomor Aset</span>
                        <span class="font-mono text-amber-400 font-bold">{{ $asset->asset_number }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[11px]">Merk & Model</span>
                        <span class="text-white font-medium">{{ $asset->brand ?? '-' }} {{ $asset->model ?? '' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[11px]">Nomor Seri Fisik</span>
                        <span class="font-mono text-slate-200">{{ $asset->serial_number ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[11px]">Tanggal Pengadaan</span>
                        <span class="text-slate-300">{{ $asset->purchase_date ? $asset->purchase_date->format('d M Y') : '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[11px]">Harga Pembelian</span>
                        <span class="text-white font-semibold">{{ $asset->purchase_price ? 'Rp ' . number_format($asset->purchase_price, 0, ',', '.') : '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[11px]">Garansi Hingga</span>
                        <span class="text-slate-300">{{ $asset->warranty_expiry_date ? $asset->warranty_expiry_date->format('d M Y') : '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[11px]">Vendor / Supplier</span>
                        <span class="text-slate-300">{{ $asset->vendor?->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[11px]">Lokasi Ruang</span>
                        <span class="text-slate-300">{{ $asset->location?->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[11px]">Pengguna Aktif</span>
                        <span class="text-slate-300 font-medium">{{ $asset->assignedTo?->name ?? 'Belum Ditugaskan' }}</span>
                    </div>
                </div>

                @if($asset->notes)
                    <div class="mt-4 pt-3 border-t border-slate-800 text-xs">
                        <span class="text-slate-500 block text-[11px] mb-1">Catatan Aset</span>
                        <p class="text-slate-300 leading-relaxed">{{ $asset->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- CMDB Configuration Items Linked to this Asset -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-800">
                    <div>
                        <h2 class="font-bold text-white text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                            <span>Hubungan Configuration Item (CMDB Hub)</span>
                        </h2>
                        <p class="text-xs text-slate-400">Komponen teknis yang berjalan / dikonfigurasi pada perangkat fisik ini</p>
                    </div>
                    <a href="{{ route('cmdb.create') }}" class="text-xs text-blue-400 hover:underline font-semibold">+ Buat CI untuk Aset Ini</a>
                </div>

                <div class="space-y-3">
                    @forelse($asset->configurationItems as $ci)
                        <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl flex items-center justify-between text-xs">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center font-bold">
                                    CI
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-blue-400 font-bold">{{ $ci->ci_code }}</span>
                                        <span class="px-1.5 py-0.2 rounded text-[10px] bg-slate-800 text-slate-300">{{ $ci->ciType->name }}</span>
                                    </div>
                                    <p class="text-white font-semibold text-sm">{{ $ci->name }}</p>
                                    <p class="text-slate-400 text-[11px] font-mono">IP: {{ $ci->ip_address ?? 'N/A' }} &bull; Hostname: {{ $ci->hostname ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <a href="{{ route('cmdb.show', $ci) }}" class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-xs font-medium">
                                Buka di CMDB &rarr;
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-6 border border-dashed border-slate-800 rounded-xl text-xs text-slate-500">
                            Aset ini belum dihubungkan dengan Configuration Item di CMDB.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Maintenance History -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5">
                <h2 class="font-bold text-white text-sm mb-4 pb-2 border-b border-slate-800 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Riwayat Pemeliharaan & Servis</span>
                </h2>

                <div class="space-y-3 text-xs">
                    @forelse($asset->maintenances as $m)
                        <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl flex items-center justify-between">
                            <div>
                                <span class="font-bold text-white">{{ $m->maintenance_type === 'preventive' ? 'Preventive Maintenance' : 'Perbaikan Corrective' }}</span>
                                <p class="text-slate-400 mt-0.5">{{ $m->description }}</p>
                                <p class="text-slate-500 text-[11px] mt-1">{{ $m->start_date->format('d M Y') }} &bull; Oleh: {{ $m->performed_by ?? 'Internal' }}</p>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-emerald-400">Rp {{ number_format($m->cost, 0, ',', '.') }}</span>
                                <span class="block text-[10px] text-slate-500 uppercase font-semibold">{{ $m->status }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 text-center py-3">Belum ada riwayat servis untuk aset ini.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Col 3: QR Code Card & Quick Inspection -->
        <div class="space-y-6">
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 text-center">
                <h3 class="font-bold text-white text-sm mb-1">QR Code Identifikasi Aset</h3>
                <p class="text-xs text-slate-400 mb-4">Pindai dengan kamera ponsel untuk inspeksi lapangan</p>

                <div class="inline-flex p-3 rounded-2xl bg-slate-950 border border-slate-800 shadow-inner mb-4">
                    {!! $qrCodeSvg !!}
                </div>

                <div class="space-y-2 text-xs">
                    <p class="font-mono text-amber-400 font-bold">{{ $asset->asset_number }}</p>
                    <p class="text-[11px] text-slate-400 truncate">{{ $scanUrl }}</p>
                    <a href="{{ route('itam.print-label', $asset) }}" target="_blank"
                        class="w-full py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-xl font-medium transition flex items-center justify-center gap-2 mt-3 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Cetak Label Stiker</span>
                    </a>
                </div>
            </div>

            <!-- Ownership & Assignment Card -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 text-xs">
                <h3 class="font-bold text-white text-sm mb-3">Penanggung Jawab Saat Ini</h3>
                <div class="space-y-2 text-slate-300">
                    <div>
                        <span class="text-slate-500 block text-[10px]">Instansi / OPD</span>
                        <span class="font-semibold text-white">{{ $asset->organization->name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[10px]">Pegawai Pemegang Aset</span>
                        <span class="font-semibold text-white">{{ $asset->assignedTo?->name ?? 'Belum Ditugaskan' }}</span>
                        @if($asset->assignedTo?->nip)
                            <span class="block text-[10px] text-slate-400">NIP: {{ $asset->assignedTo->nip }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[10px]">Lokasi Gedung / Ruang</span>
                        <span>{{ $asset->location?->name ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
