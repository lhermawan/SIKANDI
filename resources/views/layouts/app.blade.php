<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — SIKANDI Kab. Ciamis</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Vis.js Network for CMDB Graph Topology -->
    <script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex antialiased">

    <!-- Sidebar Component -->
    @include('layouts.sidebar')

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen lg:pl-64">
        <!-- Top Navbar -->
        @include('layouts.header')

        <!-- Main Body Area -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
            <!-- Flash Notifications -->
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-950/60 border border-emerald-500/30 text-emerald-300 text-sm flex items-center justify-between shadow-lg shadow-emerald-950/40">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-emerald-200">Sukses</p>
                            <p class="text-xs text-emerald-300/90">{{ session('success') }}</p>
                        </div>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-200 text-xs font-semibold px-2 py-1">✕</button>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-rose-950/60 border border-rose-500/30 text-rose-300 text-sm flex items-center justify-between shadow-lg shadow-rose-950/40">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-rose-500/20 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-rose-200">Perhatian</p>
                            <p class="text-xs text-rose-300/90">{{ session('error') }}</p>
                        </div>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-200 text-xs font-semibold px-2 py-1">✕</button>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-800/80 px-6 py-4 text-center text-xs text-slate-500 bg-slate-950/80">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
                <div>
                    <span class="font-semibold text-slate-400">SIKANDI</span> — Sistem Informasi Keamanan Informasi & Persandian
                </div>
                <div>
                    Pemerintah Kabupaten Ciamis &bull; Diskominfo &copy; {{ date('Y') }}
                </div>
            </div>
        </footer>
    </div>

    <!-- Global Search Modal (Ctrl + K) -->
    <div id="globalSearchModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm hidden flex items-start justify-center pt-20 px-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden" onclick="event.stopPropagation()">
            <div class="p-4 border-b border-slate-800 flex items-center gap-3">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="globalSearchInput" placeholder="Cari CI, Asset, Tiket, Insiden, Website, atau OPD... (ketik kata kunci)"
                    class="w-full bg-transparent border-0 text-white placeholder-slate-500 focus:outline-none text-sm"
                    onkeyup="handleGlobalSearch(this.value)">
                <button type="button" onclick="closeGlobalSearch()" class="text-xs px-2 py-1 bg-slate-800 text-slate-400 rounded-lg hover:text-white">ESC</button>
            </div>
            <div id="globalSearchResults" class="p-4 max-h-96 overflow-y-auto divide-y divide-slate-800/60 text-sm">
                <p class="text-xs text-slate-500 text-center py-6">Ketik minimal 2 karakter untuk mencari seluruh data platform...</p>
            </div>
        </div>
    </div>

    <script>
        // Keyboard shortcut Ctrl + K
        window.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                openGlobalSearch();
            }
            if (e.key === 'Escape') {
                closeGlobalSearch();
            }
        });

        function openGlobalSearch() {
            const modal = document.getElementById('globalSearchModal');
            modal.classList.remove('hidden');
            document.getElementById('globalSearchInput').focus();
        }

        function closeGlobalSearch() {
            document.getElementById('globalSearchModal').classList.add('hidden');
        }

        let searchTimeout = null;
        function handleGlobalSearch(query) {
            clearTimeout(searchTimeout);
            const resultsDiv = document.getElementById('globalSearchResults');
            if (!query || query.length < 2) {
                resultsDiv.innerHTML = '<p class="text-xs text-slate-500 text-center py-6">Ketik minimal 2 karakter untuk mencari seluruh data platform...</p>';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`{{ route('global.search') }}?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (!data || data.length === 0) {
                            resultsDiv.innerHTML = '<p class="text-xs text-slate-400 text-center py-6">Tidak ada data yang cocok dengan pencarian.</p>';
                            return;
                        }

                        let html = '';
                        data.forEach(item => {
                            html += `
                                <a href="${item.url}" class="block p-2.5 hover:bg-slate-800/70 rounded-xl transition group">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-semibold text-blue-400 group-hover:text-blue-300">${item.category}</span>
                                        <span class="text-[10px] text-slate-500">${item.extra || ''}</span>
                                    </div>
                                    <p class="font-medium text-white text-sm mt-0.5">${item.title}</p>
                                    <p class="text-xs text-slate-400">${item.subtitle || ''}</p>
                                </a>
                            `;
                        });
                        resultsDiv.innerHTML = html;
                    })
                    .catch(() => {
                        resultsDiv.innerHTML = '<p class="text-xs text-rose-400 text-center py-4">Gagal memuat hasil pencarian.</p>';
                    });
            }, 300);
        }
    </script>
    @stack('scripts')
    
    @auth
    <!-- Auto Logout Form & Modal -->
    <form id="auto-logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    <div id="autoLogoutModal" class="fixed inset-0 z-[100] bg-slate-950/90 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700/80 rounded-3xl w-full max-w-sm shadow-2xl p-8 text-center space-y-5">
            <div class="w-20 h-20 bg-amber-500/20 text-amber-400 rounded-full flex items-center justify-center mx-auto mb-2 animate-pulse">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <h3 class="text-xl font-bold text-white mb-2">Sesi Akan Berakhir</h3>
                <p class="text-sm text-slate-400 leading-relaxed">Karena tidak ada aktivitas, Anda akan otomatis ter-logout dari SIKANDI dalam:</p>
            </div>
            <div class="py-2">
                <span id="logoutCountdown" class="text-5xl font-black text-amber-400 tracking-tighter">60</span>
                <span class="text-sm font-medium text-amber-400/60 block mt-1 uppercase tracking-widest">Detik</span>
            </div>
            <button type="button" onclick="resetIdleTimer()" class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold rounded-xl transition cursor-pointer shadow-lg shadow-blue-500/20 ring-1 ring-blue-500/50">
                Tetap Login
            </button>
        </div>
    </div>

    <script>
        let idleTime = 0;
        const maxIdleTime = 15 * 60; // 15 menit
        const warningTime = 14 * 60; // Muncul modal di menit ke-14
        let idleInterval;
        let countdownInterval;
        let isLogoutWarningActive = false;

        function resetIdleTimer() {
            idleTime = 0;
            if (isLogoutWarningActive) {
                document.getElementById('autoLogoutModal').classList.add('hidden');
                document.getElementById('autoLogoutModal').classList.remove('flex');
                clearInterval(countdownInterval);
                document.getElementById('logoutCountdown').innerText = '60';
                isLogoutWarningActive = false;
            }
        }

        // Reset timer on any user activity
        window.onload = resetIdleTimer;
        window.onmousemove = resetIdleTimer;
        window.onmousedown = resetIdleTimer;
        window.ontouchstart = resetIdleTimer;
        window.onclick = resetIdleTimer;
        window.onkeydown = resetIdleTimer;
        window.addEventListener('scroll', resetIdleTimer, true);

        function checkIdleTime() {
            idleTime++;
            if (idleTime >= maxIdleTime) {
                document.getElementById('auto-logout-form').submit();
            } else if (idleTime >= warningTime) {
                const modal = document.getElementById('autoLogoutModal');
                if (!isLogoutWarningActive) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    isLogoutWarningActive = true;
                    
                    let secondsLeft = maxIdleTime - idleTime;
                    document.getElementById('logoutCountdown').innerText = secondsLeft;
                    
                    countdownInterval = setInterval(() => {
                        secondsLeft--;
                        document.getElementById('logoutCountdown').innerText = secondsLeft;
                        if (secondsLeft <= 0) {
                            clearInterval(countdownInterval);
                        }
                    }, 1000);
                }
            }
        }
        
        // Check every second
        idleInterval = setInterval(checkIdleTime, 1000);
    </script>
    @endauth
</body>
</html>
