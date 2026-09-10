@extends('layouts.app')

@section('title', 'Tambah Artikel Knowledge Base')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('knowledge.index') }}" class="p-2 bg-slate-900 border border-slate-800 rounded-xl text-slate-400 hover:text-white transition cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Tambah Artikel Baru</h1>
            <p class="text-xs text-slate-400 mt-0.5">Buat panduan atau SOP baru untuk repositori knowledge base.</p>
        </div>
    </div>

    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-6 shadow-xl">
        <form action="{{ route('knowledge.store') }}" method="POST" class="space-y-6 text-sm">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1.5">
                    <label class="block text-slate-300 font-medium">Judul Artikel <span class="text-rose-400">*</span></label>
                    <input type="text" name="title" required class="w-full px-4 py-2.5 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. SOP Penanganan Insiden Ransomware">
                </div>
                
                <div class="space-y-1.5">
                    <label class="block text-slate-300 font-medium">Kategori <span class="text-rose-400">*</span></label>
                    <input type="text" name="category" required class="w-full px-4 py-2.5 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. Pedoman, Panduan Teknis, SOP">
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-slate-300 font-medium">Konten Artikel <span class="text-rose-400">*</span></label>
                <textarea name="content" required rows="10" class="w-full px-4 py-3 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Tuliskan panduan secara detail di sini..."></textarea>
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_published" id="is_published" value="1" checked class="w-4 h-4 rounded border-slate-700 bg-slate-950 text-blue-500 focus:ring-blue-500 focus:ring-offset-slate-900">
                <label for="is_published" class="text-slate-300 cursor-pointer">Publikasikan artikel ini sekarang</label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-800/80">
                <a href="{{ route('knowledge.index') }}" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl font-medium transition cursor-pointer">Batal</a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-medium shadow-lg shadow-blue-600/30 transition cursor-pointer">Simpan Artikel</button>
            </div>
        </form>
    </div>
</div>
@endsection
