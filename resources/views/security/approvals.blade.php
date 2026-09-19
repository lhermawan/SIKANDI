@extends('layouts.app')

@section('title', 'SOC Approvals')

@section('content')
<div class="mb-6">
    <div class="flex flex-col md:flex-row justify-between md:items-end gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Persetujuan Tindakan SOC (HitL)</h1>
            <p class="text-slate-400 text-sm mt-1">Daftar tindakan otomatis dan proaktif yang memerlukan verifikasi manual analis keamanan (Human-in-the-Loop).</p>
        </div>
        
        <!-- Filter Form -->
        <form action="{{ route('security.approvals.index') }}" method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari IP, Judul, atau Deskripsi..." class="bg-slate-900 border border-slate-700 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2 placeholder-slate-500 min-w-[250px]">
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                Filter
            </button>
            @if(request('search'))
                <a href="{{ route('security.approvals.index') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold rounded-lg transition">
                    Reset
                </a>
            @endif
        </form>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl text-sm font-medium">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-xl text-sm font-medium">
        {{ session('error') }}
    </div>
@endif

<form action="{{ route('security.incidents.responses.bulk-execute') }}" method="POST" id="bulk-form">
    @csrf
    
    <div class="mb-4 flex items-center justify-between bg-slate-900/50 p-3 rounded-xl border border-slate-800">
        <div class="flex items-center gap-3">
            <input type="checkbox" id="check-all" class="rounded border-slate-700 bg-slate-800 text-indigo-500 focus:ring-indigo-500/50 w-5 h-5 ml-2 cursor-pointer">
            <div class="text-sm text-slate-400">
                Pilih Semua &mdash; <span id="selected-count" class="font-bold text-white">0</span> Tindakan dipilih
            </div>
        </div>
        <button type="submit" id="btn-bulk-execute" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold uppercase rounded-lg transition shadow-lg shadow-indigo-600/20 opacity-50 cursor-not-allowed disabled:opacity-50 disabled:cursor-not-allowed" disabled onclick="return confirm('Apakah Anda yakin ingin mengeksekusi semua tindakan SOC yang dipilih secara bersamaan?');">
            ⚡ Eksekusi Massal
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($pendingActions as $action)
            <div class="bg-slate-900 border border-amber-900/50 rounded-2xl p-5 shadow-xl relative overflow-hidden flex flex-col">
                <!-- Checkbox for Bulk Action -->
                <div class="absolute top-4 left-4 z-20">
                    <input type="checkbox" name="response_ids[]" value="{{ $action->id }}" class="check-item rounded border-slate-700 bg-slate-800 text-indigo-500 focus:ring-indigo-500/50 w-5 h-5 cursor-pointer">
                </div>

                <div class="absolute right-0 top-0 text-amber-900/10 z-0">
                    <svg class="w-32 h-32 -mr-8 -mt-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                </div>
                
                <div class="relative z-10 pl-8 h-full flex flex-col">
                    <div class="flex justify-between items-start mb-3">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30 uppercase">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Menunggu Eksekusi
                        </span>
                        <span class="text-xs text-slate-500">{{ $action->created_at->diffForHumans() }}</span>
                    </div>
                    
                    <h3 class="text-base font-bold text-slate-200 mb-1">
                        {{ $action->action === 'block_ip' ? 'Blokir IP Address' : ($action->action === 'isolate_server' ? 'Isolasi Server' : $action->action) }}
                    </h3>
                    
                    @if($action->incident)
                    <p class="text-xs text-blue-400 mb-2">
                        <a href="{{ route('security.incidents.show', $action->incident) }}" class="hover:underline font-mono">
                            {{ $action->incident->incident_code }} - {{ $action->incident->title }}
                        </a>
                    </p>
                    @endif
                    
                    <p class="text-sm text-slate-400 mb-5">{{ $action->description }}</p>
                    
                    <div class="flex gap-2 mt-auto">
                        <button type="button" onclick="confirmAction('{{ route('security.incidents.responses.execute', $action->id) }}', '{{ addslashes($action->description) }}')" class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold py-2 rounded-lg transition shadow-lg shadow-indigo-600/20">
                            Setujui & Eksekusi
                        </button>
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

    @if($pendingActions->hasPages())
    <div class="mt-6">
        {{ $pendingActions->links() }}
    </div>
    @endif
</form>

<form id="execute-single-form" method="POST" style="display: none;">
    @csrf
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkAll = document.getElementById('check-all');
    const checkboxes = document.querySelectorAll('.check-item');
    const btnBulk = document.getElementById('btn-bulk-execute');
    const selectedCount = document.getElementById('selected-count');

    function updateBulkButton() {
        const checked = document.querySelectorAll('.check-item:checked').length;
        selectedCount.textContent = checked;
        if (checked > 0) {
            btnBulk.disabled = false;
            btnBulk.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            btnBulk.disabled = true;
            btnBulk.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            checkboxes.forEach(cb => {
                cb.checked = checkAll.checked;
            });
            updateBulkButton();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const allChecked = document.querySelectorAll('.check-item:checked').length === checkboxes.length;
            checkAll.checked = allChecked;
            updateBulkButton();
        });
    });
});

function confirmAction(url, desc) {
    if(confirm('Apakah Anda yakin ingin menyetujui dan mengeksekusi tindakan ini?\n\n' + desc)) {
        let form = document.getElementById('execute-single-form');
        form.action = url;
        form.submit();
    }
}
</script>
@endsection
