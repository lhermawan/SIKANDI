<header class="h-16 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/80 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-30">
    <div class="flex items-center gap-3">
        <!-- Mobile menu toggle -->
        <button type="button" onclick="toggleSidebar()" class="lg:hidden p-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        <!-- Global Search trigger button -->
        <button type="button" onclick="openGlobalSearch()" class="flex items-center gap-3 px-3 py-1.5 rounded-xl bg-slate-950/70 border border-slate-800 text-slate-400 hover:text-slate-200 hover:border-slate-700 transition text-xs w-48 sm:w-72">
            <svg class="w-4 h-4 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <span class="truncate">Pencarian global...</span>
            <kbd class="hidden sm:inline-block ml-auto text-[10px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 border border-slate-700 font-mono">Ctrl+K</kbd>
        </button>
    </div>

    <div class="flex items-center gap-3 sm:gap-4">
        <!-- Organization / OPD Badge -->
        @if(auth()->user()->organization)
            <div class="hidden md:flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700/60 text-xs text-slate-300">
                <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span class="font-medium max-w-[200px] truncate">{{ auth()->user()->organization->name }}</span>
            </div>
        @endif

        <!-- Role Badge -->
        <span class="text-[11px] font-semibold px-2.5 py-1 rounded-lg border 
            @if(auth()->user()->hasRole('Super Admin')) bg-purple-500/20 text-purple-300 border-purple-500/30
            @elseif(auth()->user()->hasRole('Admin Persandian')) bg-cyan-500/20 text-cyan-300 border-cyan-500/30
            @elseif(auth()->user()->hasRole('IT Technician')) bg-emerald-500/20 text-emerald-300 border-emerald-500/30
            @elseif(auth()->user()->hasRole('Management')) bg-indigo-500/20 text-indigo-300 border-indigo-500/30
            @else bg-blue-500/20 text-blue-300 border-blue-500/30
            @endif">
            {{ auth()->user()->roles->first()?->name ?? 'Pengguna' }}
        </span>

        <!-- System Clock / NOC Badge -->
        <div class="hidden sm:flex items-center gap-1.5 text-xs text-slate-400 border-l border-slate-800 pl-3">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span id="headerClock" class="font-mono text-[11px] text-slate-300">--:--:-- WIB</span>
        </div>
    </div>
</header>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('-translate-x-full');
    }

    // Live clock
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', { timeZone: 'Asia/Jakarta' });
        const clockEl = document.getElementById('headerClock');
        if (clockEl) {
            clockEl.textContent = timeString + ' WIB';
        }
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>
