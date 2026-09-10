@extends('layouts.app')

@section('title', 'Form Assessment IKASANDI — ' . $organization->name)

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('ikasandi.dashboard') }}" class="hover:underline">IKASANDI</a>
                <span>/</span>
                <span class="text-white">Form Penilaian Mandiri</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Kuesioner Evaluasi Keamanan Informasi (IKASANDI)</h1>
            <p class="text-xs text-slate-400 mt-0.5">OPD: <span class="text-cyan-400 font-bold">{{ $organization->name }}</span> &bull; Tahun: {{ $assessment->year }}</p>
        </div>

        <!-- OPD Switcher for Admins -->
        @hasanyrole('Super Admin|Admin Persandian')
            <form action="{{ route('ikasandi.assessment') }}" method="GET" class="flex items-center gap-2 text-xs">
                <label class="text-slate-400 font-medium">Pilih OPD:</label>
                <select name="org_id" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-900 border border-slate-700 rounded-xl text-white">
                    @foreach($organizations as $o)
                        <option value="{{ $o->id }}" {{ $organization->id == $o->id ? 'selected' : '' }}>{{ $o->name }}</option>
                    @endforeach
                </select>
            </form>
        @endhasanyrole
    </div>

    <!-- Current Score Banner -->
    <div class="p-4 bg-slate-900 border border-slate-800 rounded-2xl flex items-center justify-between">
        <div>
            <span class="text-xs text-slate-400">Status Pengisian:</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-slate-800 text-cyan-300 ml-2">{{ $assessment->status }}</span>
        </div>
        <div class="flex items-center gap-4 text-xs">
            <div>
                <span class="text-slate-400">Skor Kepatuhan:</span>
                <span class="text-lg font-bold text-emerald-400 ml-1">{{ $assessment->compliance_score }}%</span>
            </div>
            <div>
                <span class="text-slate-400">Skor Risiko:</span>
                <span class="text-lg font-bold text-rose-400 ml-1">{{ $assessment->risk_score }}%</span>
            </div>
        </div>
    </div>

    <!-- Questionnaire Form -->
    <form action="{{ route('ikasandi.assessment.submit', $assessment) }}" method="POST" class="space-y-6">
        @csrf

        @foreach($categories as $cat)
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 sm:p-6 space-y-4">
                <div class="border-b border-slate-800 pb-3">
                    <span class="text-[10px] font-bold text-blue-400 uppercase tracking-wider block">Kategori Indikator</span>
                    <h2 class="text-base font-bold text-white mt-0.5">{{ $cat->name }}</h2>
                </div>

                <div class="space-y-5">
                    @foreach($cat->questions as $q)
                        @php
                            $currentAns = $existingAnswers[$q->id] ?? 'non_compliant';
                            $currentNote = $existingNotes[$q->id] ?? '';
                        @endphp
                        <div class="p-4 bg-slate-950/60 border border-slate-800 rounded-xl space-y-3 text-xs">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <span class="font-mono text-[10px] font-bold text-cyan-400 bg-cyan-950/40 px-2 py-0.5 rounded border border-cyan-800/40">{{ $q->code }}</span>
                                    <p class="font-semibold text-white text-sm mt-1.5">{{ $q->question }}</p>
                                    @if($q->explanation)
                                        <p class="text-slate-400 mt-1 leading-relaxed">{{ $q->explanation }}</p>
                                    @endif
                                    @if($q->guidance)
                                        <p class="text-amber-400/80 text-[11px] mt-1 italic font-medium">Panduan bukti: {{ $q->guidance }}</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Radio Options -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-2 border-t border-slate-800/80">
                                <label class="flex items-center gap-2 p-2.5 rounded-lg bg-slate-900 border border-slate-800 hover:border-emerald-500/50 cursor-pointer">
                                    <input type="radio" name="answers[{{ $q->id }}]" value="compliant" {{ $currentAns === 'compliant' ? 'checked' : '' }} class="text-emerald-500">
                                    <span class="font-medium text-emerald-300">Compliant (100%)</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-lg bg-slate-900 border border-slate-800 hover:border-amber-500/50 cursor-pointer">
                                    <input type="radio" name="answers[{{ $q->id }}]" value="partial" {{ $currentAns === 'partial' ? 'checked' : '' }} class="text-amber-500">
                                    <span class="font-medium text-amber-300">Partial (50%)</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-lg bg-slate-900 border border-slate-800 hover:border-rose-500/50 cursor-pointer">
                                    <input type="radio" name="answers[{{ $q->id }}]" value="non_compliant" {{ $currentAns === 'non_compliant' ? 'checked' : '' }} class="text-rose-500">
                                    <span class="font-medium text-rose-300">Non-Compliant (0%)</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-lg bg-slate-900 border border-slate-800 hover:border-slate-500/50 cursor-pointer">
                                    <input type="radio" name="answers[{{ $q->id }}]" value="not_applicable" {{ $currentAns === 'not_applicable' ? 'checked' : '' }} class="text-slate-400">
                                    <span class="font-medium text-slate-400">N/A (Tidak Berlaku)</span>
                                </label>
                            </div>

                            <!-- Notes -->
                            <div>
                                <input type="text" name="notes[{{ $q->id }}]" value="{{ $currentNote }}" placeholder="Catatan bukti dukung / tautan dokumen SOP pendukung..."
                                    class="w-full px-3 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white text-[11px]">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="flex items-center justify-end gap-3 sticky bottom-4 bg-slate-900/90 backdrop-blur-md p-4 rounded-2xl border border-slate-800 shadow-2xl">
            <button type="submit" name="save_draft" value="1" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition cursor-pointer">
                Simpan Draf Sementara
            </button>
            <button type="submit" name="submit_final" value="1" onclick="return confirm('Apakah Anda yakin ingin menyelesaikan dan mengajukan penilaian IKASANDI ini?')" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition shadow-lg shadow-emerald-600/30 cursor-pointer">
                Hitung Skor & Ajukan Final
            </button>
        </div>
    </form>
</div>
@endsection
