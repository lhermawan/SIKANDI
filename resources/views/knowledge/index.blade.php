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
        <div>
            <a href="{{ route('knowledge.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-lg shadow-blue-600/30 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Artikel</span>
            </a>
        </div>
    </div>

    <!-- Feedback messages -->
    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 rounded-xl text-xs flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

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
                    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 hover:border-blue-500/40 transition flex flex-col justify-between group">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-2 py-0.5 rounded text-[10px] bg-slate-800 text-cyan-300 font-bold uppercase border border-slate-700">{{ $art->category }}</span>
                                <span class="text-[11px] text-slate-500">{{ $art->created_at->format('d M Y') }} &bull; Oleh: {{ $art->author->name }}</span>
                            </div>
                            <h3 class="text-base font-bold text-white mb-2">{{ $art->title }}</h3>
                            <p class="text-xs text-slate-300 leading-relaxed line-clamp-3 mb-4">{{ Str::limit(strip_tags($art->content), 200) }}</p>
                        </div>
                        
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('knowledge.edit', $art) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-[11px] font-medium transition cursor-pointer">Edit</a>
                            <form action="{{ route('knowledge.destroy', $art) }}" method="POST" onsubmit="return confirm('Hapus artikel ini?');" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 rounded-lg text-[11px] font-medium transition cursor-pointer">Hapus</button>
                            </form>
                        </div>
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
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-white text-base">Berkas SOP & Regulasi</h2>
                <button onclick="document.getElementById('uploadDocModal').classList.remove('hidden')" class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-blue-400 rounded-lg text-xs font-semibold transition cursor-pointer">
                    + Upload
                </button>
            </div>
            
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 space-y-3">
                @forelse($documents as $doc)
                <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl text-xs flex items-center justify-between group">
                    <div>
                        <span class="font-bold text-white block">{{ $doc->document_code }}</span>
                        <span class="text-slate-400 text-[11px]">{{ $doc->title }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] px-2 py-0.5 rounded bg-blue-500/20 text-blue-300">{{ strtoupper(pathinfo($doc->file_path, PATHINFO_EXTENSION)) }}</span>
                        
                        <div class="hidden group-hover:flex items-center gap-1">
                            <a href="{{ route('documents.download', $doc) }}" class="p-1 text-slate-400 hover:text-blue-400" title="Download">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </a>
                            <form action="{{ route('documents.destroy', $doc) }}" method="POST" onsubmit="return confirm('Hapus dokumen ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 text-slate-400 hover:text-rose-400" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center text-slate-500 text-xs py-4">Belum ada dokumen yang diunggah.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Modal Upload Document -->
    <div id="uploadDocModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Upload Dokumen SOP</h3>
                <button type="button" onclick="document.getElementById('uploadDocModal').classList.add('hidden')" class="text-slate-400 hover:text-white cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-300 font-medium mb-1">Judul Dokumen *</label>
                    <input type="text" name="title" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. SOP Penanganan Insiden">
                </div>
                <div>
                    <label class="block text-slate-300 font-medium mb-1">Kategori *</label>
                    <input type="text" name="category" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. SOP, Panduan, Regulasi">
                </div>
                <div>
                    <label class="block text-slate-300 font-medium mb-1">File Dokumen * (Max 10MB)</label>
                    <input type="file" name="file" required accept=".pdf,.doc,.docx,.xls,.xlsx" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" onclick="document.getElementById('uploadDocModal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-medium cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-semibold cursor-pointer">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
