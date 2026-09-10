@extends('layouts.app')

@section('title', 'IT Asset Management (ITAM)')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>IT Management</span>
                <span>/</span>
                <span class="text-white">ITAM</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">IT Asset Management (ITAM)</h1>
            <p class="text-xs text-slate-400 mt-0.5">Siklus hidup aset TIK fisik, pencatatan garansi, pemeliharaan, dan QR barcode label</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('itam.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-lg shadow-blue-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Daftarkan Aset Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-4">
        <form action="{{ route('itam.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
            <div class="lg:col-span-2 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor aset, nama, merk, serial..."
                    class="w-full pl-9 pr-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <div>
                <select name="category" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200">
                    <option value="">Semua Kategori Aset</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="status" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200">
                    <option value="">Semua Status Siklus</option>
                    <option value="in_use" {{ request('status') == 'in_use' ? 'selected' : '' }}>In Use (Digunakan)</option>
                    <option value="inventory" {{ request('status') == 'inventory' ? 'selected' : '' }}>Inventory (Gudang)</option>
                    <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="repair" {{ request('status') == 'repair' ? 'selected' : '' }}>Repair</option>
                    <option value="disposed" {{ request('status') == 'disposed' ? 'selected' : '' }}>Disposed (Hapus)</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-medium transition cursor-pointer text-center">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'category', 'status', 'condition', 'org']))
                    <a href="{{ route('itam.index') }}" class="py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition text-center">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Assets Table -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Nomor Aset</th>
                        <th class="py-3 px-4">Nama Aset & Merk</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">OPD / Pengguna</th>
                        <th class="py-3 px-4">Status Siklus</th>
                        <th class="py-3 px-4">Kondisi</th>
                        <th class="py-3 px-4">Terkait CMDB</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($assets as $asset)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-amber-400">
                                <a href="{{ route('itam.show', $asset) }}" class="hover:underline flex items-center gap-1.5">
                                    <span>{{ $asset->asset_number }}</span>
                                </a>
                            </td>
                            <td class="py-3.5 px-4">
                                <p class="font-semibold text-white text-sm">{{ $asset->name }}</p>
                                <p class="text-[11px] text-slate-400 font-mono">{{ $asset->brand }} {{ $asset->model }} @if($asset->serial_number) &bull; SN: {{ $asset->serial_number }} @endif</p>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-800 text-slate-300 border border-slate-700">
                                    {{ $asset->category->name }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <p class="font-medium text-slate-200">{{ $asset->organization->name }}</p>
                                <p class="text-[10px] text-slate-500">{{ $asset->assignedTo?->name ?? 'Belum Ditugaskan' }}</p>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase
                                    @if($asset->lifecycle_status === 'in_use') bg-emerald-500/20 text-emerald-300 border border-emerald-500/30
                                    @elseif($asset->lifecycle_status === 'maintenance') bg-indigo-500/20 text-indigo-300 border border-indigo-500/30
                                    @elseif($asset->lifecycle_status === 'repair') bg-amber-500/20 text-amber-300 border border-amber-500/30
                                    @elseif($asset->lifecycle_status === 'disposed') bg-slate-800 text-slate-400
                                    @else bg-blue-500/20 text-blue-300 @endif">
                                    {{ $asset->lifecycle_status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-[10px] font-bold uppercase
                                    @if($asset->condition === 'good') text-emerald-400
                                    @elseif($asset->condition === 'light_damage') text-yellow-400
                                    @else text-rose-400 @endif">
                                    {{ $asset->condition }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($asset->configurationItems->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($asset->configurationItems as $ci)
                                            <a href="{{ route('cmdb.show', $ci) }}" class="px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-400 border border-blue-500/20 text-[10px] font-mono hover:underline">
                                                {{ $ci->ci_code }}
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-600 italic">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('itam.show', $asset) }}" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-xs font-medium transition">
                                        Detail
                                    </a>
                                    <a href="{{ route('itam.print-label', $asset) }}" target="_blank" class="p-1 text-slate-400 hover:text-amber-300 rounded transition" title="Cetak Label QR">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500">Belum ada aset terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($assets->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $assets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
