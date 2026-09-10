@extends('layouts.app')

@section('title', 'Portal OPD')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between bg-slate-900 border border-slate-800 p-6 rounded-2xl">
        <div>
            <span class="text-xs font-semibold text-blue-400 uppercase">Portal Layanan OPD</span>
            <h1 class="text-2xl font-bold text-white tracking-tight mt-0.5">{{ $org?->name ?? 'OPD Kabupaten Ciamis' }}</h1>
            <p class="text-xs text-slate-400 mt-1">Kelola aset perangkat dinas, tiket permohonan layanan, dan evaluasi IKASANDI</p>
        </div>
        <div>
            <a href="{{ route('service-desk.tickets') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Ajukan Permohonan / Tiket</span>
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-xs text-slate-400">Total Aset Tercatat</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['total_assets'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-xs text-slate-400">Item CMDB OPD</p>
            <p class="text-2xl font-bold text-cyan-400 mt-1">{{ $stats['total_ci'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-xs text-slate-400">Tiket Terbuka</p>
            <p class="text-2xl font-bold text-amber-400 mt-1">{{ $stats['open_tickets'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <p class="text-xs text-slate-400">Skor IKASANDI</p>
            <p class="text-2xl font-bold text-indigo-400 mt-1">{{ $stats['compliance_score'] }}%</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- My Tickets -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-white text-sm">Tiket Permohonan Terakhir</h2>
                <a href="{{ route('service-desk.tickets') }}" class="text-xs text-blue-400 hover:underline">Lihat Semua</a>
            </div>
            <div class="space-y-3">
                @forelse($myTickets as $t)
                    <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl flex items-center justify-between">
                        <div>
                            <span class="font-mono text-xs font-semibold text-blue-400">{{ $t->ticket_number }}</span>
                            <p class="text-sm font-medium text-white">{{ $t->title }}</p>
                            <p class="text-xs text-slate-500">{{ $t->created_at->format('d M Y') }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded text-xs font-semibold uppercase bg-slate-800 text-slate-300">{{ $t->status }}</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-4">Belum ada tiket diajukan.</p>
                @endforelse
            </div>
        </div>

        <!-- My Assets -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-white text-sm">Aset Perangkat Terdaftar</h2>
                <a href="{{ route('itam.index') }}" class="text-xs text-blue-400 hover:underline">Lihat Semua</a>
            </div>
            <div class="space-y-3">
                @forelse($myAssets as $asset)
                    <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl flex items-center justify-between">
                        <div>
                            <span class="font-mono text-xs font-semibold text-amber-400">{{ $asset->asset_number }}</span>
                            <p class="text-sm font-medium text-white">{{ $asset->name }}</p>
                            <p class="text-xs text-slate-500">{{ $asset->brand }} &bull; Kondisi: {{ $asset->condition }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded text-xs font-semibold uppercase bg-emerald-500/20 text-emerald-300">{{ $asset->lifecycle_status }}</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-4">Belum ada aset terdaftar pada OPD Anda.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
