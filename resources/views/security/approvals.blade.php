@extends('layouts.app')

@section('title', 'SOC Approvals')

@section('content')
<div class="mb-6">
    <div class="flex flex-col md:flex-row justify-between md:items-end gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Persetujuan Tindakan SOC (HitL)</h1>
            <p class="text-slate-400 text-sm mt-1">Daftar tindakan otomatis dan proaktif yang memerlukan verifikasi manual analis keamanan (Human-in-the-Loop).</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse( as )
        <div class="bg-slate-900 border border-amber-900/50 rounded-2xl p-5 shadow-xl relative overflow-hidden">
            <div class="absolute right-0 top-0 text-amber-900/10">
                <svg class="w-32 h-32 -mr-8 -mt-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            </div>
            
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-3">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30 uppercase">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Menunggu Eksekusi
                    </span>
                    <span class="text-xs text-slate-500">{{ ->created_at->diffForHumans() }}</span>
                </div>
                
                <h3 class="text-base font-bold text-slate-200 mb-1">
                    {{ ->action === 'block_ip' ? 'Blokir IP Address' : (->action === 'isolate_server' ? 'Isolasi Server' : ->action) }}
                </h3>
                
                @if(->incident)
                <p class="text-xs text-blue-400 mb-2">
                    <a href="{{ route('security.incidents.show', ->incident) }}" class="hover:underline font-mono">
                        {{ ->incident->incident_code }} - {{ ->incident->title }}
                    </a>
                </p>
                @endif
                
                <p class="text-sm text-slate-400 mb-5">{{ ->description }}</p>
                
                <div class="flex gap-2 mt-auto">
                    <button onclick="confirmAction('{{ route('security.incidents.responses.execute', ->id) }}', '{{ addslashes(->description) }}')" class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold py-2 rounded-lg transition shadow-lg shadow-indigo-600/20">
                        Setujui & Eksekusi
                    </button>
                    <!-- Fitur Reject Opsional, bisa pakai route execute dengan params atau manual delete -->
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full bg-slate-900 border border-slate-800 rounded-2xl p-12 text-center">
            <div class="flex flex-col items-center justify-center text-slate-500">
                <span class="text-emerald-500/30 text-5xl block mb-4">
                    <svg class="w-16 h-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
                <h3 class="text-lg font-medium text-slate-400 mb-1">Tidak Ada Antrean</h3>
                <p class="text-sm">Semua tindakan telah dieksekusi atau belum ada insiden yang memerlukan persetujuan.</p>
            </div>
        </div>
    @endforelse
</div>

@if(->hasPages())
<div class="mt-6">
    {{ ->links() }}
</div>
@endif

<form id="execute-form" method="POST" style="display: none;">
    @csrf
</form>

<script>
function confirmAction(url, desc) {
    if(confirm('Apakah Anda yakin ingin menyetujui dan mengeksekusi tindakan ini?\n\n' + desc)) {
        let form = document.getElementById('execute-form');
        form.action = url;
        form.submit();
    }
}
</script>
@endsection

