<aside class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900/95 backdrop-blur-xl border-r border-slate-800/80 flex flex-col transition-transform duration-300 lg:translate-x-0 -translate-x-full" id="sidebar">
    <!-- Logo Branding -->
    <div class="h-16 flex items-center gap-3 px-5 border-b border-slate-800/80">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-cyan-400 p-0.5 shadow-md shadow-blue-500/20">
            <div class="w-full h-full bg-slate-900 rounded-[10px] flex items-center justify-center">
                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
        </div>
        <div>
            <span class="font-bold text-base text-white tracking-tight flex items-center gap-1.5">
                SIKANDI
                <span class="text-[9px] px-1.5 py-0.2 rounded bg-blue-500/20 text-blue-300 border border-blue-500/30">Ciamis</span>
            </span>
            <p class="text-[10px] text-slate-400 leading-tight">Persandian & Keamanan</p>
        </div>
    </div>

    <!-- Navigation Scroll -->
    <div class="flex-1 overflow-y-auto px-3 py-4 space-y-5 text-xs">
        <!-- Main Dashboard -->
        <div>
            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl font-medium transition {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Dashboard</span>
            </a>
        </div>

        <!-- Section: IT MANAGEMENT -->
        <div class="space-y-1">
            <p class="px-3 text-[10px] font-bold text-slate-500 tracking-wider uppercase">IT Management</p>
            
            <!-- CMDB Dropdown/Group -->
            <div class="space-y-0.5">
                <a href="{{ route('cmdb.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-xl transition {{ request()->routeIs('cmdb.index', 'cmdb.show', 'cmdb.create', 'cmdb.edit') ? 'bg-slate-800 text-blue-400 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <span class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                        <span>CMDB — Items</span>
                    </span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-slate-800 text-slate-400">{{ \App\Models\ConfigurationItem::count() }}</span>
                </a>

                <a href="{{ route('cmdb.relationships') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('cmdb.relationships') ? 'bg-slate-800 text-blue-400 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    <span>CI Relationships</span>
                </a>

                <a href="{{ route('cmdb.graph') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-xl transition {{ request()->routeIs('cmdb.graph') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <span class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>CMDB Graph Topology</span>
                    </span>
                    <span class="text-[9px] px-1.5 py-0.2 rounded bg-emerald-500/20 text-emerald-300">Live</span>
                </a>
            </div>

            <!-- IT Asset Management -->
            <div class="space-y-0.5 pt-1">
                <a href="{{ route('itam.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-xl transition {{ request()->routeIs('itam.index', 'itam.show') ? 'bg-slate-800 text-blue-400 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <span class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                        <span>IT Asset (ITAM)</span>
                    </span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-slate-800 text-slate-400">{{ \App\Models\Asset::count() }}</span>
                </a>
            </div>

            <!-- Service Desk -->
            <div class="space-y-0.5 pt-1">
                <a href="{{ route('service-desk.tickets') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-xl transition {{ request()->routeIs('service-desk.tickets') ? 'bg-slate-800 text-blue-400 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <span class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                        <span>Service Desk (Tiket)</span>
                    </span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-blue-500/20 text-blue-300">{{ \App\Models\Ticket::whereIn('status', ['open', 'assigned', 'in_progress'])->count() }}</span>
                </a>
            </div>
        </div>

        <!-- Section: MONITORING & INCIDENT -->
        <div class="space-y-1">
            <p class="px-3 text-[10px] font-bold text-slate-500 tracking-wider uppercase">Monitoring & Incident</p>
            
            <a href="{{ route('monitoring.websites') }}"
                class="flex items-center justify-between px-3 py-2 rounded-xl transition {{ request()->routeIs('monitoring.websites') ? 'bg-slate-800 text-blue-400 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                <span class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                    <span>Website & SSL Monitor</span>
                </span>
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            </a>

            <a href="{{ route('agents.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('agents.*') ? 'bg-slate-800 text-blue-400 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                <span>Server Agents</span>
            </a>

            <a href="{{ route('incidents.index') }}"
                class="flex items-center justify-between px-3 py-2 rounded-xl transition {{ request()->routeIs('incidents.index') ? 'bg-slate-800 text-blue-400 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                <span class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Incident Management</span>
                </span>
                <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-rose-500/20 text-rose-300">{{ \App\Models\Incident::where('status', '!=', 'closed')->count() }}</span>
            </a>
        </div>

        <!-- Section: SECURITY & PERSANDIAN -->
        <div class="space-y-1">
            <p class="px-3 text-[10px] font-bold text-slate-500 tracking-wider uppercase">Security & Persandian</p>
            
            <a href="{{ route('security.incidents') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('security.incidents') ? 'bg-slate-800 text-blue-400 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.618 5.984A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016zM12 9v2m0 4h.01"/></svg>
                <span>Insiden CSIRT</span>
            </a>

            <a href="{{ route('security.risks') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('security.risks') ? 'bg-slate-800 text-blue-400 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Manajemen Risiko</span>
            </a>
        </div>

        <!-- Section: IKASANDI -->
        <div class="space-y-1">
            <p class="px-3 text-[10px] font-bold text-slate-500 tracking-wider uppercase">IKASANDI (Indikator Keamanan)</p>
            
            <a href="{{ route('ikasandi.dashboard') }}"
                class="flex items-center justify-between px-3 py-2 rounded-xl transition {{ request()->routeIs('ikasandi.dashboard') ? 'bg-indigo-600/20 text-indigo-300 border border-indigo-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                <span class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>IKASANDI Dashboard</span>
                </span>
                <span class="text-[9px] px-1.5 py-0.2 rounded bg-indigo-500/20 text-indigo-300">Score</span>
            </a>

            <a href="{{ route('ikasandi.assessment') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('ikasandi.assessment') ? 'bg-slate-800 text-blue-400 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span>Assessment OPD</span>
            </a>
        </div>

        <!-- Section: KNOWLEDGE & DOCUMENTS -->
        <div class="space-y-1">
            <p class="px-3 text-[10px] font-bold text-slate-500 tracking-wider uppercase">Knowledge & Docs</p>
            
            <a href="{{ route('knowledge.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('knowledge.*') ? 'bg-slate-800 text-blue-400 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span>Knowledge Base (SOP)</span>
            </a>
        </div>

        <!-- Section: ADMINISTRATION -->
        @hasanyrole('Super Admin|Admin Persandian')
        <div class="space-y-1 pt-2 border-t border-slate-800/80">
            <p class="px-3 text-[10px] font-bold text-slate-500 tracking-wider uppercase">Administrasi</p>
            
            <a href="{{ route('admin.organizations.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.organizations.*') ? 'bg-slate-800 text-blue-400 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Daftar OPD Ciamis</span>
            </a>

            <a href="{{ route('admin.users.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.users.*') ? 'bg-slate-800 text-blue-400 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>Manajemen User</span>
            </a>

            <a href="{{ route('admin.audit-logs') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.audit-logs') ? 'bg-slate-800 text-blue-400 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Audit Trail (Log)</span>
            </a>
        </div>
        @endhasanyrole
    </div>

    <!-- User Mini Profile & Logout -->
    <div class="p-3 border-t border-slate-800/80 bg-slate-900/60">
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-lg bg-blue-600/30 text-blue-400 border border-blue-500/30 flex items-center justify-center font-bold text-xs shrink-0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name ?? 'Pengguna' }}</p>
                    <p class="text-[10px] text-slate-400 truncate">{{ auth()->user()->roles->first()?->name ?? 'User' }}</p>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" title="Keluar" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-950/40 transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </div>
</aside>
