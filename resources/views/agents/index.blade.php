@extends('layouts.app')

@section('title', 'Server Monitoring Agents')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-white tracking-tight">Monitoring Agents</h1>
        <p class="text-sm text-slate-400 mt-1">Kelola agent monitoring server dan integrasinya dengan CMDB.</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 relative overflow-hidden group">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent opacity-0 group-hover:opacity-100 transition"></div>
        <p class="text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Total Agent</p>
        <p class="text-2xl font-bold text-white">{{ $agents->total() }}</p>
    </div>
    
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 flex items-center justify-between col-span-1 md:col-span-2">
        <div>
            <p class="text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Global Registration Token</p>
            <div class="flex items-center gap-3">
                <code class="px-2 py-1 bg-slate-950 border border-slate-800 rounded text-sm text-emerald-400">
                    {{ $registrationToken ?? 'Belum ada token registrasi' }}
                </code>
            </div>
            <p class="text-[11px] text-slate-500 mt-2">Gunakan token ini saat menginstall agent baru pada server.</p>
        </div>
        <div>
            <form action="{{ route('agents.token') }}" method="POST">
                @csrf
                <button class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-4 py-2 rounded-lg font-medium transition">
                    Generate Baru
                </button>
            </form>
        </div>
    </div>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead>
                <tr class="bg-slate-950 border-b border-slate-800 text-slate-400 text-xs font-semibold uppercase tracking-wider">
                    <th class="px-6 py-4">Agent ID</th>
                    <th class="px-6 py-4">Hostname</th>
                    <th class="px-6 py-4">Linked CI Server</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Last Seen</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @forelse($agents as $agent)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-6 py-4">
                            <span class="font-medium text-slate-200">{{ $agent->agent_id }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-white">{{ $agent->hostname }}</span>
                                <span class="text-xs text-slate-500">{{ $agent->ip_address }} • {{ $agent->os }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($agent->ci_id)
                                <a href="{{ route('cmdb.show', $agent->configurationItem) }}" class="text-blue-400 hover:text-blue-300 text-sm">
                                    {{ $agent->configurationItem->name }}
                                </a>
                            @else
                                <span class="text-xs px-2 py-0.5 rounded-md bg-slate-800 text-slate-400 border border-slate-700">Unlinked</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($agent->status === 'online')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Online
                                </span>
                            @elseif($agent->status === 'warning')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Warning
                                </span>
                            @elseif($agent->status === 'pending')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Pending Review
                                </span>
                            @elseif($agent->status === 'offline')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Offline
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-500/10 text-slate-400 border border-slate-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span> {{ ucfirst($agent->status) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-400 text-sm">
                            {{ $agent->last_seen_at ? $agent->last_seen_at->diffForHumans() : '-' }}
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('agents.show', $agent) }}" class="text-blue-400 hover:text-blue-300 text-sm font-medium">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500 text-sm">
                            Belum ada agent yang terdaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($agents->hasPages())
        <div class="px-6 py-4 border-t border-slate-800">
            {{ $agents->links() }}
        </div>
    @endif
</div>
@endsection
