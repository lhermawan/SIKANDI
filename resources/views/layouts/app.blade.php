<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — SIKANDI Kab. Ciamis</title>
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
</body>
</html>
