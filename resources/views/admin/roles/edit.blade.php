@extends('layouts.app')
@section('title', 'Edit Role: ' . $role->name)

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.roles.index') }}" class="p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Administrasi</span><span>/</span><a href="{{ route('admin.roles.index') }}" class="hover:text-slate-300">Role & Akses</a><span>/</span><span class="text-white">Edit</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Edit Role: {{ $role->name }}</h1>
        </div>
    </div>

    <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        @csrf
        @method('PUT')
        <div class="p-6 border-b border-slate-800">
            <label for="name" class="block text-xs font-semibold text-slate-400 mb-2">NAMA ROLE <span class="text-rose-500">*</span></label>
            @php $isProtected = in_array($role->name, ['Super Admin', 'Admin Persandian', 'IT Technician', 'OPD User', 'Management']); @endphp
            <input type="text" name="name" id="name" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition placeholder-slate-600 {{ $isProtected ? 'opacity-50 cursor-not-allowed' : '' }}" value="{{ old('name', $role->name) }}" {{ $isProtected ? 'readonly' : '' }}>
            @if($isProtected)
                <p class="text-[10px] text-slate-500 mt-1.5">Nama role bawaan sistem tidak dapat diubah.</p>
            @endif
            @error('name')
                <p class="text-rose-500 text-xs mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div class="p-6">
            <div class="mb-4">
                <h3 class="text-sm font-semibold text-white">Hak Akses (Permissions)</h3>
                <p class="text-xs text-slate-400 mt-1">Pilih menu dan fitur apa saja yang dapat diakses oleh role ini.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @php
                    $rolePermissions = $role->permissions->pluck('id')->toArray();
                @endphp
                @foreach($permissions as $permission)
                <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-800 bg-slate-950 hover:border-slate-700 cursor-pointer transition">
                    <div class="flex items-center h-5 mt-0.5">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }} class="w-4 h-4 bg-slate-900 border-slate-700 rounded text-blue-600 focus:ring-blue-500/30 focus:ring-offset-slate-950">
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-medium text-slate-300">{{ $permission->name }}</span>
                        <span class="text-[10px] text-slate-500">ID: {{ $permission->id }}</span>
                    </div>
                </label>
                @endforeach
            </div>
            @error('permissions')
                <p class="text-rose-500 text-xs mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="p-4 bg-slate-800/30 border-t border-slate-800 flex items-center justify-end gap-3">
            <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-xl transition">Batal</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-blue-600/30 transition">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
