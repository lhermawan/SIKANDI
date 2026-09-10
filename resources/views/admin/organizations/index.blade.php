@extends('layouts.app')

@section('title', 'Manajemen OPD & Unit Kerja')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Administrasi</span>
                <span>/</span>
                <span class="text-white">OPD & Instansi</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Manajemen OPD & Unit Kerja Ciamis</h1>
            <p class="text-xs text-slate-400 mt-0.5">Katalog seluruh Organisasi Perangkat Daerah, Badan, Bagian Setda, RSUD, dan Kecamatan Kabupaten Ciamis</p>
        </div>
        <div>
            <button onclick="document.getElementById('addOrgModal').classList.remove('hidden')" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-lg shadow-blue-600/30 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Tambah OPD / Unit</span>
            </button>
        </div>
    </div>

    <!-- Feedback messages -->
    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 rounded-xl text-xs flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Filter Bar -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-4">
        <form action="{{ route('admin.organizations.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <div class="lg:col-span-2 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama OPD, singkatan, kepala dinas..."
                    class="w-full pl-9 pr-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <div>
                <select name="category" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Kategori</option>
                    <option value="dinas" {{ request('category') == 'dinas' ? 'selected' : '' }}>Dinas</option>
                    <option value="badan" {{ request('category') == 'badan' ? 'selected' : '' }}>Badan</option>
                    <option value="bagian_setda" {{ request('category') == 'bagian_setda' ? 'selected' : '' }}>Bagian Setda</option>
                    <option value="rsud" {{ request('category') == 'rsud' ? 'selected' : '' }}>RSUD</option>
                    <option value="kecamatan" {{ request('category') == 'kecamatan' ? 'selected' : '' }}>Kecamatan</option>
                    <option value="lainnya" {{ request('category') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-medium transition cursor-pointer text-center">
                    Cari
                </button>
                @if(request()->anyFilled(['search', 'category']))
                    <a href="{{ route('admin.organizations.index') }}" class="py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition text-center">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Organizations Table Card -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 text-slate-400 uppercase tracking-wider border-b border-slate-800/80">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Kode & Nama Instansi</th>
                        <th class="py-3 px-4 font-semibold">Kategori</th>
                        <th class="py-3 px-4 font-semibold">Pimpinan / NIP</th>
                        <th class="py-3 px-4 font-semibold">Kontak & Website</th>
                        <th class="py-3 px-4 font-semibold text-center">Relasi CMDB</th>
                        <th class="py-3 px-4 font-semibold text-center">Aset IT</th>
                        <th class="py-3 px-4 font-semibold text-center">User</th>
                        <th class="py-3 px-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($organizations as $org)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4">
                                <div class="font-semibold text-white">{{ $org->name }}</div>
                                <div class="text-[11px] font-mono text-blue-400">{{ $org->code }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-semibold bg-slate-800 text-slate-300 border border-slate-700 uppercase">
                                    {{ str_replace('_', ' ', $org->category) }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-slate-200">{{ $org->head_name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $org->head_nip ? 'NIP: '.$org->head_nip : '' }}</div>
                            </td>
                            <td class="py-3 px-4 text-[11px] text-slate-400">
                                @if($org->email) <div>{{ $org->email }}</div> @endif
                                @if($org->phone) <div>{{ $org->phone }}</div> @endif
                                @if($org->website_url)
                                    <a href="{{ $org->website_url }}" target="_blank" class="text-blue-400 hover:underline flex items-center gap-1 mt-0.5">
                                        <span>{{ Str::limit($org->website_url, 25) }}</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                    {{ $org->configuration_items_count }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                    {{ $org->assets_count }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    {{ $org->users_count }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <button type="button" onclick='openEditModal(@json($org))' class="text-blue-400 hover:text-blue-300 transition text-[11px] font-semibold bg-blue-500/10 px-3 py-1.5 rounded-lg border border-blue-500/20">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">
                                Belum ada data OPD yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($organizations->hasPages())
            <div class="p-4 border-t border-slate-800/80">
                {{ $organizations->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tambah Organisasi -->
    <div id="addOrgModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Tambah OPD / Unit Kerja Baru</h3>
                <button type="button" onclick="document.getElementById('addOrgModal').classList.add('hidden')" class="text-slate-400 hover:text-white cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.organizations.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">Kode Singkat *</label>
                        <input type="text" name="code" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white uppercase focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. DINKES">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-slate-300 font-medium mb-1">Nama Resmi OPD *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. Dinas Kesehatan Kabupaten Ciamis">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">Kategori Instansi *</label>
                        <select name="category" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="dinas">Dinas</option>
                            <option value="badan">Badan</option>
                            <option value="bagian_setda">Bagian Setda</option>
                            <option value="rsud">RSUD</option>
                            <option value="kecamatan">Kecamatan</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">Website URL</label>
                        <input type="url" name="website_url" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="https://dinkes.ciamiskab.go.id">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">Nama Kepala OPD / Pejabat</label>
                        <input type="text" name="head_name" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. Dr. H. Yoyo, M.Kes">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">NIP Kepala OPD</label>
                        <input type="text" name="head_nip" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="1968XXXXXXXXXX">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">Email Resmi</label>
                        <input type="email" name="email" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="dinkes@ciamiskab.go.id">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">No. Telp Kantor</label>
                        <input type="text" name="phone" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="(0265) 77XXXX">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-300 font-medium mb-1">Alamat Kantor</label>
                    <textarea name="address" rows="2" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Jl. Mr. Iwa Kusuma Somantri No. ... Ciamis"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" onclick="document.getElementById('addOrgModal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-medium cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-semibold shadow-md shadow-blue-600/30 cursor-pointer">Simpan OPD</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal Edit Organisasi -->
    <div id="editOrgModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Edit OPD / Unit Kerja</h3>
                <button type="button" onclick="document.getElementById('editOrgModal').classList.add('hidden')" class="text-slate-400 hover:text-white cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="editOrgForm" action="" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">Kode Singkat *</label>
                        <input type="text" id="edit_code" name="code" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white uppercase focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-slate-300 font-medium mb-1">Nama Resmi OPD *</label>
                        <input type="text" id="edit_name" name="name" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">Kategori Instansi *</label>
                        <select id="edit_category" name="category" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="dinas">Dinas</option>
                            <option value="badan">Badan</option>
                            <option value="bagian_setda">Bagian Setda</option>
                            <option value="rsud">RSUD</option>
                            <option value="kecamatan">Kecamatan</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">Website URL</label>
                        <input type="url" id="edit_website_url" name="website_url" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">Nama Kepala OPD / Pejabat</label>
                        <input type="text" id="edit_head_name" name="head_name" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">NIP Kepala OPD</label>
                        <input type="text" id="edit_head_nip" name="head_nip" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">Email Resmi</label>
                        <input type="email" id="edit_email" name="email" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">No. Telp Kantor</label>
                        <input type="text" id="edit_phone" name="phone" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-300 font-medium mb-1">Alamat Kantor</label>
                    <textarea id="edit_address" name="address" rows="2" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" onclick="document.getElementById('editOrgModal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-medium cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-semibold shadow-md shadow-blue-600/30 cursor-pointer">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditModal(org) {
        document.getElementById('editOrgForm').action = `/admin/organizations/${org.id}`;
        document.getElementById('edit_code').value = org.code || '';
        document.getElementById('edit_name').value = org.name || '';
        document.getElementById('edit_category').value = org.category || 'dinas';
        document.getElementById('edit_website_url').value = org.website_url || '';
        document.getElementById('edit_head_name').value = org.head_name || '';
        document.getElementById('edit_head_nip').value = org.head_nip || '';
        document.getElementById('edit_email').value = org.email || '';
        document.getElementById('edit_phone').value = org.phone || '';
        document.getElementById('edit_address').value = org.address || '';
        
        document.getElementById('editOrgModal').classList.remove('hidden');
    }
</script>
@endsection
