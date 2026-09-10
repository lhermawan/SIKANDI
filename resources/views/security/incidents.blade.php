@extends('layouts.app')

@section('title', 'Insiden Keamanan Informasi (CSIRT)')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Security & CSIRT</span>
                <span>/</span>
                <span class="text-white">Insiden Siber</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Tanggap Darurat Insiden Siber (CSIRT Ciamis)</h1>
            <p class="text-xs text-slate-400 mt-0.5">Penanganan insiden malware, web defacement, kebocoran data, phising, dan peretasan akun</p>
        </div>
        <div>
            <a href="{{ route('security.incidents.create') }}" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-lg shadow-red-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Lapor Insiden Siber Baru</span>
            </a>
        </div>
    </div>

    <!-- Security Incidents Table -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Kode CSIRT</th>
                        <th class="py-3 px-4">Judul & Jenis Serangan</th>
                        <th class="py-3 px-4">OPD Korban</th>
                        <th class="py-3 px-4">CI Terkait</th>
                        <th class="py-3 px-4">Tingkat Keparahan</th>
                        <th class="py-3 px-4">Alur Kerja (Workflow)</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($incidents as $sec)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-rose-400">
                                {{ $sec->incident_code }}
                            </td>
                            <td class="py-3.5 px-4">
                                <p class="font-semibold text-white text-sm">{{ $sec->title }}</p>
                                <span class="px-2 py-0.2 rounded text-[10px] bg-slate-800 text-slate-300 border border-slate-700 uppercase font-semibold">
                                    {{ str_replace('_', ' ', $sec->incident_type) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-slate-200 font-medium">{{ $sec->organization->name }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($sec->configurationItem)
                                    <a href="{{ route('cmdb.show', $sec->configurationItem) }}" class="font-mono text-blue-400 hover:underline">
                                        {{ $sec->configurationItem->ci_code }}
                                    </a>
                                @else
                                    <span class="text-slate-600">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    @if($sec->severity === 'critical') bg-rose-500/20 text-rose-300
                                    @elseif($sec->severity === 'high') bg-orange-500/20 text-orange-300
                                    @else bg-yellow-500/20 text-yellow-300 @endif">
                                    {{ $sec->severity }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-slate-800 text-slate-300">
                                    {{ $sec->workflow_status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <span class="text-slate-400">{{ $sec->created_at->format('d M Y') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">Tidak ada insiden keamanan informasi aktif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
