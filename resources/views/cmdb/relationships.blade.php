@extends('layouts.app')

@section('title', 'CI Relationships (Hubungan Dua Arah)')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('cmdb.index') }}" class="hover:underline">CMDB</a>
                <span>/</span>
                <span class="text-white">Relationships</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">CMDB Configuration Item Relationships</h1>
            <p class="text-xs text-slate-400 mt-0.5">Struktur relasi ketergantungan dua arah antar komponen infrastruktur dan aplikasi</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('cmdb.graph') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold transition border border-slate-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Lihat di Graph</span>
            </a>
            <button type="button" onclick="openRelModal()" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-lg shadow-blue-600/30 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Hubungan Relasi</span>
            </button>
        </div>
    </div>

    <!-- Relationships Table -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Source CI (Sumber)</th>
                        <th class="py-3 px-4 text-center">Hubungan Relasi Logis</th>
                        <th class="py-3 px-4">Target CI (Tujuan)</th>
                        <th class="py-3 px-4">Keterangan</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($relationships as $rel)
                        <tr class="hover:bg-slate-800/30 transition">
                            <!-- Source CI -->
                            <td class="py-3.5 px-4">
                                <a href="{{ route('cmdb.show', $rel->sourceCi) }}" class="font-bold text-white hover:text-blue-400 hover:underline">
                                    {{ $rel->sourceCi->name }}
                                </a>
                                <div class="flex items-center gap-1.5 mt-0.5 text-[10px] text-slate-400 font-mono">
                                    <span>{{ $rel->sourceCi->ci_code }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $rel->sourceCi->ciType->name }}</span>
                                </div>
                            </td>

                            <!-- Relation Type Badge with arrow -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/30 text-indigo-300 font-semibold text-[11px]">
                                    <span>{{ $rel->human_type }}</span>
                                    <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </div>
                                <span class="block text-[10px] text-slate-500 mt-0.5">Inverse: {{ $rel->inverse_human_type }}</span>
                            </td>

                            <!-- Target CI -->
                            <td class="py-3.5 px-4">
                                <a href="{{ route('cmdb.show', $rel->targetCi) }}" class="font-bold text-white hover:text-blue-400 hover:underline">
                                    {{ $rel->targetCi->name }}
                                </a>
                                <div class="flex items-center gap-1.5 mt-0.5 text-[10px] text-slate-400 font-mono">
                                    <span>{{ $rel->targetCi->ci_code }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $rel->targetCi->ciType->name }}</span>
                                </div>
                            </td>

                            <!-- Description -->
                            <td class="py-3.5 px-4 text-slate-400">
                                {{ $rel->description ?? '-' }}
                            </td>

                            <!-- Action -->
                            <td class="py-3.5 px-4 text-right">
                                <form action="{{ route('cmdb.relationships.destroy', $rel) }}" method="POST" onsubmit="return confirm('Hapus relasi ini?')" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-slate-500 hover:text-rose-400 transition" title="Hapus Relasi">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500">Belum ada relasi CMDB yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($relationships->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $relationships->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Tambah Relasi -->
<div id="relModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg shadow-2xl p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-800">
            <div>
                <h3 class="font-bold text-white text-base">Buat Relasi Antar CI</h3>
                <p class="text-xs text-slate-400">Pilih dua Configuration Item dan tentukan tipe hubungannya</p>
            </div>
            <button type="button" onclick="closeRelModal()" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form action="{{ route('cmdb.relationships.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-medium text-slate-300 mb-1">Source CI (Sumber) *</label>
                <select name="source_ci_id" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                    <option value="">-- Pilih Source CI --</option>
                    @foreach($allCis as $src)
                        <option value="{{ $src->id }}">[{{ $src->ci_code }}] {{ $src->name }} ({{ $src->ciType->name }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-medium text-slate-300 mb-1">Jenis Relasi Logis *</label>
                <select name="relationship_type" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                    <option value="hosted_on">Hosted On (Dihosting Pada Server / VM)</option>
                    <option value="runs_on">Runs On (Berjalan Di Atas OS / Container)</option>
                    <option value="uses">Uses (Menggunakan Database / Layanan)</option>
                    <option value="depends_on">Depends On (Bergantung Pada)</option>
                    <option value="connects_to">Connects To (Terhubung Ke Switch / Router)</option>
                    <option value="protected_by">Protected By (Dilindungi Oleh Firewall / WAF)</option>
                    <option value="contains">Contains (Memuat / Berisi)</option>
                    <option value="managed_by">Managed By (Dikelola Oleh)</option>
                    <option value="located_at">Located At (Berlokasi Di)</option>
                    <option value="supports">Supports (Mendukung)</option>
                    <option value="part_of">Part Of (Bagian Dari)</option>
                </select>
            </div>

            <div>
                <label class="block font-medium text-slate-300 mb-1">Target CI (Tujuan) *</label>
                <select name="target_ci_id" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
                    <option value="">-- Pilih Target CI --</option>
                    @foreach($allCis as $target)
                        <option value="{{ $target->id }}">[{{ $target->ci_code }}] {{ $target->name }} ({{ $target->ciType->name }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-medium text-slate-300 mb-1">Deskripsi Relasi (Opsional)</label>
                <input type="text" name="description" placeholder="Contoh: Interkoneksi Fiber Optic Port 12 / Direct dependency"
                    class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeRelModal()" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl hover:bg-slate-700 transition">Batal</button>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-medium transition cursor-pointer">Simpan Relasi</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRelModal() {
        document.getElementById('relModal').classList.remove('hidden');
    }
    function closeRelModal() {
        document.getElementById('relModal').classList.add('hidden');
    }
</script>
@endsection
