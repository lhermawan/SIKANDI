@extends('layouts.app')
@section('title', 'Manajemen Pengguna & RBAC')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1"><span>Administrasi</span><span>/</span><span class="text-white">Pengguna & RBAC</span></div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Manajemen Pengguna & Hak Akses</h1>
            <p class="text-xs text-slate-400 mt-0.5">Kelola akun personel Diskominfo, teknisi persandian, PIC OPD, dan hak akses sistem SIKANDI</p>
        </div>
        <button onclick="openAddModal()" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-lg shadow-blue-600/30 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            <span>Tambah Pengguna</span>
        </button>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 rounded-xl text-xs flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-500/10 border border-rose-500/30 text-rose-300 rounded-xl text-xs flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 bg-rose-500/10 border border-rose-500/30 text-rose-300 rounded-xl text-xs space-y-1">
            <div class="font-semibold">Terdapat kesalahan input:</div>
            <ul class="list-disc list-inside space-y-0.5 text-rose-400">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 text-slate-400 uppercase tracking-wider border-b border-slate-800/80">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Pengguna</th>
                        <th class="py-3 px-4 font-semibold">Peran (Role)</th>
                        <th class="py-3 px-4 font-semibold">OPD / Instansi</th>
                        <th class="py-3 px-4 font-semibold">NIP / Kontak</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 font-semibold">Terdaftar</th>
                        <th class="py-3 px-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center font-bold text-white text-xs shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-white flex items-center gap-1.5">
                                            {{ $user->name }}
                                            @if(isset($user->locked_until) && $user->locked_until && now()->lt($user->locked_until))
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">LOCKED</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-400">&#64;{{ $user->username }} &bull; {{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                @forelse($user->roles as $role)
                                    @php
                                        $bc = match($role->name) {
                                            'Super Admin' => 'bg-rose-500/10 text-rose-300 border-rose-500/30',
                                            'Admin Persandian' => 'bg-indigo-500/10 text-indigo-300 border-indigo-500/30',
                                            'IT Technician' => 'bg-amber-500/10 text-amber-300 border-amber-500/30',
                                            'OPD User' => 'bg-emerald-500/10 text-emerald-300 border-emerald-500/30',
                                            'Management' => 'bg-purple-500/10 text-purple-300 border-purple-500/30',
                                            default => 'bg-slate-500/10 text-slate-300 border-slate-500/30'
                                        };
                                    @endphp
                                    <span class="inline-block px-2.5 py-1 rounded-md text-[10px] font-semibold border {{ $bc }}">{{ $role->name }}</span>
                                @empty
                                    <span class="text-slate-500 italic">No role</span>
                                @endforelse
                            </td>
                            <td class="py-3 px-4">
                                @if($user->organization)
                                    <div class="font-medium text-slate-200">{{ $user->organization->name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $user->organization->code }}</div>
                                @else
                                    <span class="text-slate-500 italic">Diskominfo / Global</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-400">
                                <div>NIP: {{ $user->nip ?? '-' }}</div>
                                <div class="text-[11px]">{{ $user->phone ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Aktif</span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20"><span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Nonaktif</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-400 text-[11px]">{{ $user->created_at?->isoFormat('D MMM Y') ?? '-' }}</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button onclick="openEditModal({{ $user->id }})" title="Edit" class="p-1.5 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 border border-blue-500/20 transition cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <form action="{{ route('admin.users.toggle-active', $user) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}" class="p-1.5 rounded-lg {{ $user->is_active ? 'bg-amber-500/10 text-amber-400 hover:bg-amber-500/20 border border-amber-500/20' : 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 border border-emerald-500/20' }} transition cursor-pointer">
                                            @if($user->is_active)
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            @else
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @endif
                                        </button>
                                    </form>
                                    @if(isset($user->locked_until) && $user->locked_until && now()->lt($user->locked_until))
                                        <form action="{{ route('admin.users.unlock', $user) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" title="Buka Kunci" class="p-1.5 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 border border-rose-500/20 transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Hapus pengguna ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Hapus" class="p-1.5 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 border border-rose-500/20 transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-slate-500">Belum ada data pengguna.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="p-4 border-t border-slate-800/80">{{ $users->links() }}</div>
        @endif
    </div>
    {{-- MODAL TAMBAH --}}
    <div id="addUserModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-4 max-h-screen overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Tambah Pengguna Baru</h3>
                <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden')" class="text-slate-400 hover:text-white cursor-pointer text-xl">&times;</button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-slate-300 font-medium mb-1">Nama Lengkap *</label><input type="text" name="name" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                    <div><label class="block text-slate-300 font-medium mb-1">Username *</label><input type="text" name="username" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. ahmad.fauzi"></div>
                </div>
                <div><label class="block text-slate-300 font-medium mb-1">Email Resmi *</label><input type="email" name="email" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="ahmad@ciamiskab.go.id"></div>
                <div>
                    <label class="block text-slate-300 font-medium mb-1">Password Awal * <span class="text-slate-500 font-normal">(min. 12 karakter, huruf besar, angka, simbol)</span></label>
                    <div class="relative">
                        <input type="text" name="password" id="addPassword" required autocomplete="new-password" class="w-full px-3 py-2 pr-28 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white font-mono text-xs focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Min. 12 karakter">
                        <button type="button" onclick="generatePassword('addPassword','addStrengthBar','addStrength','add')" class="absolute right-1.5 top-1/2 -translate-y-1/2 px-2 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-[10px] font-bold transition">⚡ Generate</button>
                    </div>
                    <div class="mt-1.5 h-1.5 rounded-full bg-slate-800 overflow-hidden"><div id="addStrengthBar" class="h-full rounded-full transition-all duration-300 w-0 bg-slate-600"></div></div>
                    <div class="mt-1 flex items-center justify-between">
                        <span id="addStrengthLabel" class="text-[10px] text-slate-500">—</span>
                        <button type="button" id="addCopyBtn" onclick="copyPassword('addPassword')" class="hidden text-[10px] text-blue-400 hover:text-blue-300">📋 Salin</button>
                    </div>
                    <div id="addReqBox" class="hidden mt-2 grid grid-cols-2 gap-1 p-2 bg-slate-950/50 rounded-xl border border-slate-800 text-[10px]">
                        <span id="add-req-len" class="text-slate-500">✗ Min. 12 karakter</span>
                        <span id="add-req-upper" class="text-slate-500">✗ Huruf besar (A-Z)</span>
                        <span id="add-req-num" class="text-slate-500">✗ Angka (0-9)</span>
                        <span id="add-req-sym" class="text-slate-500">✗ Simbol (!@#$...)</span>
                    </div>
                    <input type="hidden" name="password_confirmation" id="addPasswordConfirm">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-slate-300 font-medium mb-1">Role *</label><select name="role" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">@foreach($roles as $r)<option value="{{ $r->name }}">{{ $r->name }}</option>@endforeach</select></div>
                    <div><label class="block text-slate-300 font-medium mb-1">OPD / Instansi</label><select name="organization_id" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500"><option value="">-- Diskominfo / Pusat --</option>@foreach($organizations as $org)<option value="{{ $org->id }}">{{ $org->code }} - {{ $org->name }}</option>@endforeach</select></div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-slate-300 font-medium mb-1">NIP</label><input type="text" name="nip" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="19850101XXXXXXXX"></div>
                    <div><label class="block text-slate-300 font-medium mb-1">No. HP / WA</label><input type="text" name="phone" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="08XXXXXXXXXX"></div>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-medium cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-semibold cursor-pointer">Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT --}}
    <div id="editUserModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-4 max-h-screen overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div>
                    <h3 class="text-base font-bold text-white">Edit Pengguna</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5" id="editModalSubtitle">—</p>
                </div>
                <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-white cursor-pointer text-xl">&times;</button>
            </div>
            <form id="editUserForm" method="POST" class="space-y-3 text-xs">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-slate-300 font-medium mb-1">Nama Lengkap *</label><input type="text" name="name" id="editName" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                    <div><label class="block text-slate-300 font-medium mb-1">Username *</label><input type="text" name="username" id="editUsername" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                </div>
                <div><label class="block text-slate-300 font-medium mb-1">Email Resmi *</label><input type="email" name="email" id="editEmail" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div>
                    <label class="block text-slate-300 font-medium mb-1">Password Baru <span class="text-slate-500 font-normal">(kosongkan jika tidak berubah)</span></label>
                    <div class="relative">
                        <input type="text" name="password" id="editPassword" autocomplete="new-password" class="w-full px-3 py-2 pr-28 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white font-mono text-xs focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Min. 12 karakter">
                        <button type="button" onclick="generatePassword('editPassword','editStrengthBar','editStrength','edit')" class="absolute right-1.5 top-1/2 -translate-y-1/2 px-2 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-[10px] font-bold transition">⚡ Generate</button>
                    </div>
                    <div class="mt-1.5 h-1.5 rounded-full bg-slate-800 overflow-hidden"><div id="editStrengthBar" class="h-full rounded-full transition-all duration-300 w-0 bg-slate-600"></div></div>
                    <div class="mt-1 flex items-center justify-between">
                        <span id="editStrengthLabel" class="text-[10px] text-slate-500">—</span>
                        <button type="button" id="editCopyBtn" onclick="copyPassword('editPassword')" class="hidden text-[10px] text-blue-400 hover:text-blue-300">📋 Salin</button>
                    </div>
                    <div id="editReqBox" class="hidden mt-2 grid grid-cols-2 gap-1 p-2 bg-slate-950/50 rounded-xl border border-slate-800 text-[10px]">
                        <span id="edit-req-len" class="text-slate-500">✗ Min. 12 karakter</span>
                        <span id="edit-req-upper" class="text-slate-500">✗ Huruf besar (A-Z)</span>
                        <span id="edit-req-num" class="text-slate-500">✗ Angka (0-9)</span>
                        <span id="edit-req-sym" class="text-slate-500">✗ Simbol (!@#$...)</span>
                    </div>
                    <input type="hidden" name="password_confirmation" id="editPasswordConfirm">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-slate-300 font-medium mb-1">Role *</label><select name="role" id="editRole" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">@foreach($roles as $r)<option value="{{ $r->name }}">{{ $r->name }}</option>@endforeach</select></div>
                    <div><label class="block text-slate-300 font-medium mb-1">OPD / Instansi</label><select name="organization_id" id="editOrganization" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500"><option value="">-- Diskominfo / Pusat --</option>@foreach($organizations as $org)<option value="{{ $org->id }}">{{ $org->code }} - {{ $org->name }}</option>@endforeach</select></div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-slate-300 font-medium mb-1">NIP</label><input type="text" name="nip" id="editNip" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                    <div><label class="block text-slate-300 font-medium mb-1">No. HP / WA</label><input type="text" name="phone" id="editPhone" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-medium cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-semibold cursor-pointer">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Toast --}}
    <div id="toast" class="fixed bottom-6 right-6 z-[100] hidden items-center gap-2 px-4 py-3 bg-emerald-600 text-white rounded-xl shadow-xl text-xs font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span id="toastMsg">Disalin!</span>
    </div>
</div>
@endsection
@push('scripts')
<script>
function generatePassword(inputId, barId, labelId, prefix) {
    const u='ABCDEFGHIJKLMNOPQRSTUVWXYZ', l='abcdefghijklmnopqrstuvwxyz', n='0123456789', s='!@#$%^&*_-+=?';
    let pwd = [
        u[Math.floor(Math.random()*u.length)], u[Math.floor(Math.random()*u.length)],
        l[Math.floor(Math.random()*l.length)], l[Math.floor(Math.random()*l.length)],
        n[Math.floor(Math.random()*n.length)], n[Math.floor(Math.random()*n.length)],
        s[Math.floor(Math.random()*s.length)], s[Math.floor(Math.random()*s.length)]
    ];
    const all = u+l+n+s;
    for(let i=pwd.length;i<16;i++) pwd.push(all[Math.floor(Math.random()*all.length)]);
    pwd = pwd.sort(()=>Math.random()-0.5).join(');
    document.getElementById(inputId).value = pwd;
    const cf = document.getElementById(prefix+'PasswordConfirm');
    if(cf) cf.value = pwd;
    checkStrength(inputId, barId, labelId, prefix);
    const cb = document.getElementById(prefix+'CopyBtn');
    if(cb) cb.classList.remove('hidden');
}

function checkStrength(inputId, barId, labelId, prefix) {
    const val = document.getElementById(inputId).value;
    const rb = document.getElementById(prefix+'ReqBox');
    if(rb){ rb.classList.remove('hidden'); rb.classList.add('grid'); }
    const checks = { len: val.length>=12, upper: /[A-Z]/.test(val), num: /[0-9]/.test(val), sym: /[!@#$%^&*_\-+=?]/.test(val) };
    const labels = { len:'Min. 12 karakter', upper:'Huruf besar (A-Z)', num:'Angka (0-9)', sym:'Simbol (!@#$...)' };
    Object.keys(checks).forEach(k=>{
        const el=document.getElementById(prefix+'-req-'+k);
        if(el){ el.className=checks[k]?'text-emerald-400':'text-slate-500'; el.textContent=(checks[k]?'✓ ':'✗ ')+labels[k]; }
    });
    const score = Object.values(checks).filter(Boolean).length;
    const map=[
        {w:'0%',c:'bg-slate-600',t:'—',tc:'text-slate-500'},
        {w:'25%',c:'bg-rose-500',t:'Sangat Lemah',tc:'text-rose-400'},
        {w:'50%',c:'bg-amber-500',t:'Lemah',tc:'text-amber-400'},
        {w:'75%',c:'bg-yellow-400',t:'Cukup',tc:'text-yellow-400'},
        {w:'100%',c:'bg-emerald-500',t:'Kuat ✓',tc:'text-emerald-400'},
    ];
    const bar=document.getElementById(barId);
    const lbl=document.getElementById(labelId+'Label');
    if(bar){bar.style.width=map[score].w;bar.className='h-full rounded-full transition-all duration-300 '+map[score].c;}
    if(lbl){lbl.textContent=map[score].t;lbl.className='text-[10px] '+map[score].tc;}
    const cf=document.getElementById(prefix+'PasswordConfirm');
    if(cf) cf.value=document.getElementById(inputId).value;
}

['add','edit'].forEach(p=>{
    const el=document.getElementById(p+'Password');
    if(el) el.addEventListener('input',()=>checkStrength(p+'Password',p+'StrengthBar',p+'Strength',p));
});

function copyPassword(inputId){
    const v=document.getElementById(inputId).value;
    if(!v) return;
    navigator.clipboard.writeText(v).then(()=>showToast('Password berhasil disalin ke clipboard!'));
}

function showToast(msg){
    const t=document.getElementById('toast');
    document.getElementById('toastMsg').textContent=msg;
    t.classList.remove('hidden');t.classList.add('flex');
    setTimeout(()=>{t.classList.add('hidden');t.classList.remove('flex');},3000);
}

function openAddModal(){ document.getElementById('addUserModal').classList.remove('hidden'); }

function openEditModal(userId){
    fetch('/admin/users/'+userId+'/edit', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r=>r.json())
        .then(d=>{
            document.getElementById('editUserForm').action='/admin/users/'+d.id;
            document.getElementById('editModalSubtitle').textContent='@'+d.username+' · '+d.email;
            document.getElementById('editName').value=d.name||';
            document.getElementById('editUsername').value=d.username||';
            document.getElementById('editEmail').value=d.email||';
            document.getElementById('editNip').value=d.nip||';
            document.getElementById('editPhone').value=d.phone||';
            document.getElementById('editPassword').value=';
            document.getElementById('editPasswordConfirm').value=';
            document.getElementById('editStrengthBar').style.width='0%';
            document.getElementById('editCopyBtn').classList.add('hidden');
            const rb=document.getElementById('editReqBox');
            if(rb){rb.classList.add('hidden');rb.classList.remove('grid');}
            if(d.role){ const s=document.getElementById('editRole'); for(let o of s.options) if(o.value===d.role){o.selected=true;break;} }
            const os=document.getElementById('editOrganization');
            for(let o of os.options) o.selected=(o.value==d.organization_id);
            document.getElementById('editUserModal').classList.remove('hidden');
        })
        .catch(()=>showToast('Gagal memuat data pengguna.'));
}

function closeEditModal(){ document.getElementById('editUserModal').classList.add('hidden'); }
</script>
@endpush
