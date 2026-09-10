@extends('layouts.app')

@section('title', 'Configuration Items (CMDB)')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>IT Management</span>
                <span>/</span>
                <span class="text-white">CMDB</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Configuration Items (CI)</h1>
            <p class="text-xs text-slate-400 mt-0.5">Katalog terpadu seluruh komponen infrastruktur, server, jaringan, dan aplikasi Kabupaten Ciamis</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('cmdb.graph') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold transition border border-slate-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Visualisasi Graph</span>
            </a>
            <a href="{{ route('cmdb.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-lg shadow-blue-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Daftarkan CI Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-4">
        <form action="{{ route('cmdb.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
            <!-- Search Keyword -->
            <div class="lg:col-span-2 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, kode CI, IP, Hostname, Domain..."
                    class="w-full pl-9 pr-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <!-- Filter Type -->
            <div>
                <select name="type" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Tipe CI</option>
                    @foreach($ciTypes as $type)
                        <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status -->
            <div>
                <select name="status" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="warning" {{ request('status') == 'warning' ? 'selected' : '' }}>Warning</option>
                    <option value="down" {{ request('status') == 'down' ? 'selected' : '' }}>Down</option>
                    <option value="planned" {{ request('status') == 'planned' ? 'selected' : '' }}>Planned</option>
                    <option value="retired" {{ request('status') == 'retired' ? 'selected' : '' }}>Retired</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-medium transition cursor-pointer text-center">
                    Terapkan
                </button>
                @if(request()->anyFilled(['search', 'type', 'status', 'criticality', 'org']))
                    <a href="{{ route('cmdb.index') }}" class="py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition text-center">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Kode CI</th>
                        <th class="py-3 px-4">Nama CI & Identitas</th>
                        <th class="py-3 px-4">Tipe CI</th>
                        <th class="py-3 px-4">Aset Terkait</th>
                        <th class="py-3 px-4">Penanggung Jawab / OPD</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Kritikalitas</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-blue-400">
                                <a href="{{ route('cmdb.show', $item) }}" class="hover:underline flex items-center gap-1.5">
                                    <span>{{ $item->ci_code }}</span>
                                </a>
                            </td>
                            <td class="py-3.5 px-4">
                                <p class="font-semibold text-white text-sm">{{ $item->name }}</p>
                                <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-400 font-mono">
                                    @if($item->ip_address) <span>IP: {{ $item->ip_address }}</span> @endif
                                    @if($item->hostname) <span>&bull; {{ $item->hostname }}</span> @endif
                                    @if($item->domain) <span>&bull; {{ $item->domain }}</span> @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase" style="background-color: {{ $item->ciType->color }}20; color: {{ $item->ciType->color }}; border: 1px solid {{ $item->ciType->color }}40;">
                                    {{ $item->ciType->name }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($item->asset)
                                    <span class="font-mono text-amber-400 text-[11px]">{{ $item->asset->asset_number }}</span>
                                    <p class="text-[10px] text-slate-500 truncate max-w-[120px]">{{ $item->asset->brand }} {{ $item->asset->model }}</p>
                                @else
                                    <span class="text-slate-600 italic">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <p class="font-medium text-slate-200">{{ $item->organization->name }}</p>
                                <p class="text-[10px] text-slate-500">{{ $item->responsible_unit ?? 'N/A' }}</p>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase
                                    @if($item->status === 'active') bg-emerald-500/20 text-emerald-300 border border-emerald-500/30
                                    @elseif($item->status === 'maintenance') bg-indigo-500/20 text-indigo-300 border border-indigo-500/30
                                    @elseif($item->status === 'warning') bg-amber-500/20 text-amber-300 border border-amber-500/30
                                    @elseif($item->status === 'down') bg-rose-500/20 text-rose-300 border border-rose-500/30
                                    @else bg-slate-700 text-slate-300 @endif">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-[10px] font-bold uppercase
                                    @if($item->criticality === 'critical') text-rose-400
                                    @elseif($item->criticality === 'high') text-orange-400
                                    @elseif($item->criticality === 'medium') text-yellow-400
                                    @else text-slate-400 @endif">
                                    {{ $item->criticality }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('cmdb.show', $item) }}" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-xs font-medium transition">
                                        Detail
                                    </a>
                                    <a href="{{ route('cmdb.edit', $item) }}" class="p-1 text-slate-400 hover:text-white rounded transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-500">
                                Tidak ada Configuration Item yang cocok dengan kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
