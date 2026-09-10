@extends('layouts.app')

@section('title', 'Security Rules')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h1 class="text-2xl font-bold text-white tracking-tight">Security Detection Rules</h1>
        <p class="text-sm text-slate-400 mt-1">Konfigurasi threshold dan parameter untuk engine deteksi SIKANDI.</p>
    </div>
    <button
        onclick="document.getElementById('modal-create-rule').classList.remove('hidden')"
        class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition"
    >
        + Tambah Rule
    </button>
</div>

{{-- Create Modal --}}
<div id="modal-create-rule" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
    <div class="bg-slate-900 border border-slate-700 rounded-2xl p-6 shadow-2xl w-full max-w-md">
        <h2 class="text-lg font-bold text-white mb-4">Tambah Security Rule Baru</h2>
        <form action="{{ route('security.rules.store') }}" method="POST" class="space-y-4 text-sm">
            @csrf
            <div>
                <label class="block text-xs text-slate-400 mb-1">Nama Rule (Contoh: SUSPICIOUS_LOGIN)</label>
                <input type="text" name="name" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Threshold</label>
                    <input type="number" name="threshold" value="5" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Time Window (s)</label>
                    <input type="number" name="time_window_seconds" value="300" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Severity</label>
                    <select name="severity" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white uppercase">
                        <option value="critical">CRITICAL</option>
                        <option value="high" selected>HIGH</option>
                        <option value="medium">MEDIUM</option>
                        <option value="low">LOW</option>
                        <option value="info">INFO</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Risk Bonus (0-100)</label>
                    <input type="number" name="risk_score" value="25" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                </div>
            </div>
            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="auto_incident" value="1" checked class="rounded border-slate-700 bg-slate-900 text-blue-600 focus:ring-0">
                    <span class="text-slate-300">Buat Insiden Otomatis</span>
                </label>
            </div>
            <div class="flex gap-3 pt-4 mt-4 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('modal-create-rule').classList.add('hidden')" class="flex-1 bg-slate-800 hover:bg-slate-700 text-white rounded-lg py-2 font-medium transition">Batal</button>
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg py-2 font-medium transition">Simpan Rule</button>
            </div>
        </form>
    </div>
</div>


<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($rules as $rule)
    <div x-data="{ editing: false }" class="bg-slate-900 border {{ $rule->enabled ? 'border-slate-700' : 'border-slate-800 opacity-60' }} rounded-2xl p-5 relative">
        <div class="flex justify-between items-start mb-4">
            <h3 class="text-lg font-bold text-white">{{ str_replace('_', ' ', $rule->name) }}</h3>
            <div class="flex items-center gap-3">
                <button @click="editing = true" class="text-slate-400 hover:text-blue-400 text-xs font-semibold">EDIT</button>
                <form action="{{ route('security.rules.toggle', $rule) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-10 h-5 rounded-full relative transition-colors {{ $rule->enabled ? 'bg-emerald-500' : 'bg-slate-700' }}">
                        <span class="absolute top-0.5 {{ $rule->enabled ? 'right-0.5' : 'left-0.5' }} w-4 h-4 bg-white rounded-full transition-all"></span>
                    </button>
                </form>
            </div>
        </div>
        
        <div x-show="!editing" class="space-y-3 text-sm">
            <div class="flex justify-between border-b border-slate-800 pb-2">
                <span class="text-slate-400">Threshold</span>
                <span class="text-white font-medium">{{ $rule->threshold }} events</span>
            </div>
            <div class="flex justify-between border-b border-slate-800 pb-2">
                <span class="text-slate-400">Time Window</span>
                <span class="text-white font-medium">{{ $rule->time_window_seconds }} seconds</span>
            </div>
            <div class="flex justify-between border-b border-slate-800 pb-2">
                <span class="text-slate-400">Severity</span>
                <span class="text-white font-medium uppercase">{{ $rule->severity }}</span>
            </div>
            <div class="flex justify-between border-b border-slate-800 pb-2">
                <span class="text-slate-400">Risk Bonus</span>
                <span class="text-rose-400 font-medium">+{{ $rule->risk_score }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Auto Incident</span>
                <span class="{{ $rule->auto_incident ? 'text-emerald-400' : 'text-slate-500' }} font-medium">
                    {{ $rule->auto_incident ? 'YES' : 'NO' }}
                </span>
            </div>
        </div>

        <form x-show="editing" x-cloak action="{{ route('security.rules.update', $rule) }}" method="POST" class="space-y-3 text-sm">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Threshold</label>
                    <input type="number" name="threshold" value="{{ $rule->threshold }}" class="w-full bg-slate-950 border border-slate-800 rounded px-2 py-1 text-white">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Time Window (s)</label>
                    <input type="number" name="time_window_seconds" value="{{ $rule->time_window_seconds }}" class="w-full bg-slate-950 border border-slate-800 rounded px-2 py-1 text-white">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Severity</label>
                    <select name="severity" class="w-full bg-slate-950 border border-slate-800 rounded px-2 py-1 text-white uppercase">
                        <option value="critical" {{ $rule->severity == 'critical' ? 'selected' : '' }}>CRITICAL</option>
                        <option value="high" {{ $rule->severity == 'high' ? 'selected' : '' }}>HIGH</option>
                        <option value="medium" {{ $rule->severity == 'medium' ? 'selected' : '' }}>MEDIUM</option>
                        <option value="low" {{ $rule->severity == 'low' ? 'selected' : '' }}>LOW</option>
                        <option value="info" {{ $rule->severity == 'info' ? 'selected' : '' }}>INFO</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Risk Bonus (0-100)</label>
                    <input type="number" name="risk_score" value="{{ $rule->risk_score }}" class="w-full bg-slate-950 border border-slate-800 rounded px-2 py-1 text-white">
                </div>
                <div class="col-span-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="auto_incident" value="1" {{ $rule->auto_incident ? 'checked' : '' }} class="rounded border-slate-700 bg-slate-900 text-blue-600 focus:ring-0">
                        <span class="text-slate-300">Buat Insiden Otomatis</span>
                    </label>
                </div>
            </div>
            <div class="flex gap-2 pt-2 border-t border-slate-800">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white rounded py-1.5 font-medium transition">Simpan</button>
                <button type="button" @click="editing = false" class="flex-1 bg-slate-800 hover:bg-slate-700 text-white rounded py-1.5 font-medium transition">Batal</button>
            </div>
        </form>
    </div>
    @endforeach
</div>
@endsection
