@extends('layouts.app')

@section('title', 'Manajemen Lokasi & Ruang')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Master Data</span>
                <span>/</span>
                <span class="text-white">Lokasi & Ruang</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Manajemen Lokasi & Ruang</h1>
            <p class="text-xs text-slate-400 mt-0.5">Katalog terpusat untuk Gedung, Lantai, dan Ruangan</p>
        </div>
        <div class="flex gap-2">
            <button onclick="openModal('location')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Tambah Lokasi (Gedung)</span>
            </button>
            <button onclick="openModal('room')" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-lg shadow-blue-600/30 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Tambah Ruangan</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 rounded-xl text-xs flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    
    @if(session('error'))
        <div class="p-4 bg-rose-500/10 border border-rose-500/30 text-rose-300 rounded-xl text-xs flex items-center gap-3">
            <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Filter Bar -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-4">
        <form action="{{ route('admin.locations.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <div class="lg:col-span-2 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama lokasi, kode, atau gedung..."
                    class="w-full pl-9 pr-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <div>
                <select name="type" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Tipe</option>
                    <option value="location" {{ request('type') == 'location' ? 'selected' : '' }}>Hanya Lokasi (Gedung)</option>
                    <option value="room" {{ request('type') == 'room' ? 'selected' : '' }}>Hanya Ruangan</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-medium transition cursor-pointer text-center">
                    Cari
                </button>
                @if(request()->anyFilled(['search', 'type']))
                    <a href="{{ route('admin.locations.index') }}" class="py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition text-center">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 text-slate-400 uppercase tracking-wider border-b border-slate-800/80">
                    <tr>
                        <th class="py-3 px-4 font-semibold w-12">Tipe</th>
                        <th class="py-3 px-4 font-semibold">Nama / Kode</th>
                        <th class="py-3 px-4 font-semibold">Lokasi Induk</th>
                        <th class="py-3 px-4 font-semibold">Detail (Gedung / Lantai)</th>
                        <th class="py-3 px-4 font-semibold text-center">Digunakan</th>
                        <th class="py-3 px-4 font-semibold text-center">Status</th>
                        <th class="py-3 px-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($locations as $loc)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4 text-center">
                                @if($loc->type === 'location')
                                    <div class="p-2 bg-blue-900/30 text-blue-400 rounded-lg inline-block border border-blue-800/50" title="Lokasi (Gedung)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    </div>
                                @else
                                    <div class="p-2 bg-amber-900/30 text-amber-400 rounded-lg inline-block border border-amber-800/50" title="Ruangan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-white text-sm">{{ $loc->name }}</div>
                                <div class="text-[11px] font-mono text-slate-400 mt-0.5">{{ $loc->code ?: '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                @if($loc->parent)
                                    <span class="px-2 py-1 bg-slate-800 border border-slate-700 rounded text-slate-300">
                                        {{ $loc->parent->name }}
                                    </span>
                                @else
                                    <span class="text-slate-500 italic">- (Root)</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-slate-300">Gedung: {{ $loc->building ?: '-' }}</div>
                                <div class="text-slate-400">Lantai: {{ $loc->floor ?: '-' }}</div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if($loc->type === 'location')
                                        <span title="Sub Ruangan" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/10 text-purple-400 border border-purple-500/20">
                                            {{ $loc->children_count }} Ruang
                                        </span>
                                    @endif
                                    <span title="CMDB Items" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                        {{ $loc->configuration_items_count }} CI
                                    </span>
                                    <span title="Aset IT" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        {{ $loc->assets_count }} Aset
                                    </span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($loc->is_active)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Aktif</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">Nonaktif</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" onclick='openEditModal(@json($loc))' class="text-blue-400 hover:text-blue-300 transition text-[11px] font-semibold bg-blue-500/10 px-2 py-1.5 rounded-lg border border-blue-500/20">
                                        Edit
                                    </button>
                                    
                                    <form action="{{ route('admin.locations.toggle', $loc) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-[11px] font-semibold px-2 py-1.5 rounded-lg border transition {{ $loc->is_active ? 'text-amber-400 hover:text-amber-300 bg-amber-500/10 border-amber-500/20' : 'text-emerald-400 hover:text-emerald-300 bg-emerald-500/10 border-emerald-500/20' }}">
                                            {{ $loc->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    @if($loc->assets_count == 0 && $loc->configuration_items_count == 0 && $loc->children_count == 0)
                                        <form action="{{ route('admin.locations.destroy', $loc) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-400 hover:text-rose-300 transition text-[11px] font-semibold bg-rose-500/10 px-2 py-1.5 rounded-lg border border-rose-500/20">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">
                                Belum ada data lokasi atau ruang.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($locations->hasPages())
            <div class="p-4 border-t border-slate-800/80">
                {{ $locations->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form (Tambah/Edit) -->
    <div id="locationModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 id="modalTitle" class="text-base font-bold text-white">Tambah Data</h3>
                <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-white cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="locationForm" action="{{ route('admin.locations.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="type" id="formType" value="location">
                
                <div id="parentWrapper" class="hidden">
                    <label class="block text-slate-300 font-medium mb-1">Lokasi Induk (Gedung) *</label>
                    <select id="parent_id" name="parent_id" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Lokasi Induk --</option>
                        @foreach($allLocations as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }} {{ $parent->code ? '('.$parent->code.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2">
                        <label id="nameLabel" class="block text-slate-300 font-medium mb-1">Nama Lokasi *</label>
                        <input type="text" id="name" name="name" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">Kode (Opsional)</label>
                        <input type="text" id="code" name="code" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white uppercase focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">Nama Gedung (Opsional)</label>
                        <input type="text" id="building" name="building" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">Lantai (Opsional)</label>
                        <input type="text" id="floor" name="floor" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-300 font-medium mb-1">Keterangan / Alamat Detail</label>
                    <textarea id="description" name="description" rows="2" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                
                <div class="pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="is_active" name="is_active" value="1" checked class="rounded bg-slate-950 border-slate-700 text-blue-600">
                        <span class="text-slate-300 font-medium">Status Aktif</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-medium cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-semibold shadow-md shadow-blue-600/30 cursor-pointer">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openModal(type = 'location') {
        const form = document.getElementById('locationForm');
        form.reset();
        form.action = "{{ route('admin.locations.store') }}";
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('formType').value = type;
        
        setupModalForType(type);
        
        document.getElementById('modalTitle').textContent = type === 'room' ? 'Tambah Ruangan' : 'Tambah Lokasi (Gedung)';
        document.getElementById('locationModal').classList.remove('hidden');
    }

    function openEditModal(data) {
        const form = document.getElementById('locationForm');
        form.action = `/admin/locations/${data.id}`;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('formType').value = data.type;
        
        setupModalForType(data.type);

        document.getElementById('name').value = data.name || '';
        document.getElementById('code').value = data.code || '';
        document.getElementById('parent_id').value = data.parent_id || '';
        document.getElementById('building').value = data.building || '';
        document.getElementById('floor').value = data.floor || '';
        document.getElementById('description').value = data.description || '';
        document.getElementById('is_active').checked = data.is_active;
        
        document.getElementById('modalTitle').textContent = data.type === 'room' ? 'Edit Ruangan' : 'Edit Lokasi';
        document.getElementById('locationModal').classList.remove('hidden');
    }
    
    function setupModalForType(type) {
        if (type === 'room') {
            document.getElementById('parentWrapper').classList.remove('hidden');
            document.getElementById('parent_id').required = true;
            document.getElementById('nameLabel').textContent = 'Nama Ruang *';
        } else {
            document.getElementById('parentWrapper').classList.add('hidden');
            document.getElementById('parent_id').required = false;
            document.getElementById('nameLabel').textContent = 'Nama Lokasi (Gedung) *';
        }
    }

    function closeModal() {
        document.getElementById('locationModal').classList.add('hidden');
    }
</script>
@endsection
