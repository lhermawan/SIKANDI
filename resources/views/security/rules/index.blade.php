@extends('layouts.app')

@section('title', 'Security Rules')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-white tracking-tight">Security Detection Rules</h1>
    <p class="text-sm text-slate-400 mt-1">Konfigurasi threshold dan parameter untuk engine deteksi SIKANDI.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($rules as $rule)
    <div class="bg-slate-900 border {{ $rule->enabled ? 'border-slate-700' : 'border-slate-800 opacity-60' }} rounded-2xl p-5 relative">
        <div class="flex justify-between items-start mb-4">
            <h3 class="text-lg font-bold text-white">{{ str_replace('_', ' ', $rule->name) }}</h3>
            <form action="{{ route('security.rules.toggle', $rule) }}" method="POST">
                @csrf
                <button type="submit" class="w-10 h-5 rounded-full relative transition-colors {{ $rule->enabled ? 'bg-emerald-500' : 'bg-slate-700' }}">
                    <span class="absolute top-0.5 {{ $rule->enabled ? 'right-0.5' : 'left-0.5' }} w-4 h-4 bg-white rounded-full transition-all"></span>
                </button>
            </form>
        </div>
        
        <div class="space-y-3 text-sm">
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
    </div>
    @endforeach
</div>
@endsection
