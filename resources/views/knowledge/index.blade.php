@extends('layouts.app')

@section('title', 'Knowledge Base & SOP')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Knowledge</span>
                <span>/</span>
                <span class="text-white">Dokumen & SOP</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Knowledge Base & Repositori SOP</h1>
            <p class="text-xs text-slate-400 mt-0.5">Panduan teknis, pedoman sandi, SOP penanganan insiden, dan petunjuk operasional TIK</p>
        </div>
    </div>

    <!-- Search Articles -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-4">
        <form action="{{ route('knowledge.index') }}" method="GET" class="flex gap-3 text-xs">
            <div class="flex-1 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari artikel SOP, troubleshooting, panduan firewall..."
                    class="w-full pl-9 pr-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>
            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-medium cursor-pointer">
                Cari
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Articles List -->
        <div class="lg:col-span-2 space-y-4">
            <h2 class="font-bold text-white text-base">Artikel Panduan & Pengetahuan</h2>

            <div class="space-y-3">
                @forelse($articles as $art)
                    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 hover:border-blue-500/40 transition">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2 py-0.5 rounded text-[10px] bg-slate-800 text-cyan-300 font-bold uppercase border border-slate-700">{{ $art->category }}</span>
                            <span class="text-[11px] text-slate-500">{{ $art->created_at->format('d M Y') }} &bull; Oleh: {{ $art->author->name }}</span>
                        </div>
                        <h3 class="text-base font-bold text-white mb-2">{{ $art->title }}</h3>
                        <p class="text-xs text-slate-300 leading-relaxed line-clamp-3">{{ Str::limit(strip_tags($art->content), 200) }}</p>
                    </div>
                @empty
                    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-8 text-center text-slate-500 text-xs">
                        Belum ada artikel panduan dalam kategori ini.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Quick SOP Documents -->
        <div class="space-y-4">
            <h2 class="font-bold text-white text-base">Berkas SOP & Regulasi</h2>
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 space-y-3">
                <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl text-xs flex items-center justify-between">
                    <div>
                        <span class="font-bold text-white block">SOP-CSIRT-01</span>
                        <span class="text-slate-400 text-[11px]">SOP Penanganan Insiden Siber 1x24 Jam</span>
                    </div>
                    <span class="text-[10px] px-2 py-0.5 rounded bg-blue-500/20 text-blue-300">PDF</span>
                </div>
                <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl text-xs flex items-center justify-between">
                    <div>
                        <span class="font-bold text-white block">SOP-SANDI-02</span>
                        <span class="text-slate-400 text-[11px]">Pedoman Penggunaan Sandi & Kriptografi</span>
                    </div>
                    <span class="text-[10px] px-2 py-0.5 rounded bg-blue-500/20 text-blue-300">PDF</span>
                </div>
                <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl text-xs flex items-center justify-between">
                    <div>
                        <span class="font-bold text-white block">SOP-BACKUP-03</span>
                        <span class="text-slate-400 text-[11px]">Prosedur Backup & Restore Data OPD</span>
                    </div>
                    <span class="text-[10px] px-2 py-0.5 rounded bg-blue-500/20 text-blue-300">PDF</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
