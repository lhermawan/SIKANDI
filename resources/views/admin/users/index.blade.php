@extends('layouts.app')

@section('title', 'Manajemen Pengguna & RBAC')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Administrasi</span>
                <span>/</span>
                <span class="text-white">Pengguna & RBAC</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Manajemen Pengguna & Hak Akses</h1>
            <p class="text-xs text-slate-400 mt-0.5">Kelola akun personel Diskominfo, teknisi persandian, PIC OPD, dan hak akses sistem SIKANDI</p>
        </div>
        <div>
            <button onclick="document.getElementById('addUserModal').classList.remove('hidden')" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-lg shadow-blue-600/30 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>Tambah Pengguna</span>
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
    @if($errors->any())
        <div class="p-4 bg-rose-500/10 border border-rose-500/30 text-rose-300 rounded-xl text-xs space-y-1">
            <div class="font-semibold">Terdapat kesalahan input:</div>
            <ul class="list-disc list-inside space-y-0.5 text-rose-400">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Users Table Card -->
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
                                        <div class="font-semibold text-white">{{ $user->name }}</div>
                                        <div class="text-[11px] text-slate-400 flex items-center gap-2">
                                            <span>&#64;{{ $user->username }}</span>
                                            <span>&bull;</span>
                                            <span>{{ $user->email }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                @forelse($user->roles as $role)
                                    @php
                                        $badgeColor = match($role->name) {
                                            'Super Admin' => 'bg-rose-500/10 text-rose-300 border-rose-500/30',
                                            'Admin Persandian' => 'bg-indigo-500/10 text-indigo-300 border-indigo-500/30',
                                            'IT Technician' => 'bg-amber-500/10 text-amber-300 border-amber-500/30',
                                            'OPD User' => 'bg-emerald-500/10 text-emerald-300 border-emerald-500/30',
                                            'Management' => 'bg-purple-500/10 text-purple-300 border-purple-500/30',
                                            default => 'bg-slate-500/10 text-slate-300 border-slate-500/30'
                                        };
                                    @endphp
                                    <span class="inline-block px-2.5 py-1 rounded-md text-[10px] font-semibold border {{ $badgeColor }}">
                                        {{ $role->name }}
                                    </span>
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
                                <div class="text-[11px] text-slate-400">{{ $user->phone ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-400 text-[11px]">
                                {{ $user->created_at ? $user->created_at->isoFormat('D MMM Y') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">
                                Belum ada data pengguna.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-800/80">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tambah Pengguna -->
    <div id="addUserModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Tambah Pengguna Baru</h3>
                <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden')" class="text-slate-400 hover:text-white cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. Ahmad Fauzi, S.Kom">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">Username *</label>
                        <input type="text" name="username" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. ahmad.fauzi">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">Email Resmi *</label>
                        <input type="email" name="email" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="ahmad@ciamiskab.go.id">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">Password Awal *</label>
                        <input type="password" name="password" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Minimal 6 karakter">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">Role / Peran *</label>
                        <select name="role" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">OPD / Instansi</label>
                        <select name="organization_id" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Diskominfo / Pusat --</option>
                            @foreach($organizations as $org)
                                <option value="{{ $org->id }}">{{ $org->code }} - {{ $org->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">NIP</label>
                        <input type="text" name="nip" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="19850101XXXXXXXX">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">No. WhatsApp / HP</label>
                        <input type="text" name="phone" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="08XXXXXXXXXX">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-medium cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-semibold shadow-md shadow-blue-600/30 cursor-pointer">Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
