<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — SIKANDI Kab. Ciamis</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-center relative overflow-hidden">
    <!-- Ambient Gradient Background -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="container mx-auto px-4 py-8 relative z-10 max-w-md">
        <!-- Logo & Branding -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-cyan-400 p-0.5 shadow-xl shadow-blue-500/20 mb-4">
                <div class="w-full h-full bg-slate-900 rounded-[14px] flex items-center justify-center">
                    <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white flex items-center justify-center gap-2">
                SIKANDI
                <span class="text-xs px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-400 border border-blue-500/30 font-medium">v1.0</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-semibold">Sistem Informasi Keamanan Informasi & Persandian</p>
            <p class="text-xs text-slate-500">Diskominfo Pemerintah Kabupaten Ciamis</p>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-800/80 rounded-2xl p-6 sm:p-8 shadow-2xl shadow-black/50">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-white">Masuk ke Portal Layanan</h2>
                <p class="text-xs text-slate-400 mt-1">Gunakan akun kredensial yang telah terdaftar untuk melanjutkan.</p>
            </div>

            @if(session('info'))
                <div class="mb-4 p-3 rounded-lg bg-blue-900/40 border border-blue-700/50 text-blue-300 text-xs flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-rose-900/40 border border-rose-700/50 text-rose-300 text-xs">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4" id="login-form">
                @csrf
                <div>
                    <label for="login" class="block text-xs font-medium text-slate-300 mb-1.5">Username atau Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                            class="w-full pl-9 pr-3 py-2.5 bg-slate-950/60 border border-slate-700/80 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            placeholder="nama@ciamis.go.id atau username">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-medium text-slate-300 mb-1.5">Kata Sandi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <input type="password" id="password" name="password" required
                            class="w-full pl-9 pr-3 py-2.5 bg-slate-950/60 border border-slate-700/80 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-400 hover:text-slate-300">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-950 border-slate-700 text-blue-600 focus:ring-blue-500">
                        <span>Ingat saya</span>
                    </label>
                    <span class="text-slate-500">Portal Sandi & CSIRT</span>
                </div>

                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                <button type="submit"
                    class="w-full py-2.5 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-medium text-sm rounded-xl shadow-lg shadow-blue-500/25 transition duration-200 flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    <span>Masuk ke Sistem</span>
                </button>
            </form>

            <!-- Quick Account Switcher (Helper for Evaluation) -->
            <!-- <div class="mt-6 pt-5 border-t border-slate-800">
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2.5 flex items-center justify-between">
                    <span>Pilih Akun Demo (1-Klik)</span>
                    <span class="text-slate-500 lowercase font-normal">pass: password</span>
                </p>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button" onclick="setLogin('superadmin@ciamis.go.id')"
                        class="px-2.5 py-1.5 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-slate-300 text-left transition border border-slate-700/50">
                        <span class="block font-semibold text-white">Super Admin</span>
                        <span class="text-[10px] text-slate-400">superadmin</span>
                    </button>
                    <button type="button" onclick="setLogin('admin.sandi@ciamis.go.id')"
                        class="px-2.5 py-1.5 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-slate-300 text-left transition border border-slate-700/50">
                        <span class="block font-semibold text-cyan-300">Admin Persandian</span>
                        <span class="text-[10px] text-slate-400">admin.sandi</span>
                    </button>
                    <button type="button" onclick="setLogin('teknisi@ciamis.go.id')"
                        class="px-2.5 py-1.5 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-slate-300 text-left transition border border-slate-700/50">
                        <span class="block font-semibold text-emerald-300">IT Technician</span>
                        <span class="text-[10px] text-slate-400">teknisi</span>
                    </button>
                    <button type="button" onclick="setLogin('opd.dinkes@ciamis.go.id')"
                        class="px-2.5 py-1.5 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-slate-300 text-left transition border border-slate-700/50">
                        <span class="block font-semibold text-amber-300">OPD User</span>
                        <span class="text-[10px] text-slate-400">opd.dinkes</span>
                    </button>
                    <button type="button" onclick="setLogin('pimpinan@ciamis.go.id')"
                        class="col-span-2 px-2.5 py-1.5 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-slate-300 text-left transition border border-slate-700/50 flex justify-between items-center">
                        <div>
                            <span class="block font-semibold text-indigo-300">Management / Pimpinan</span>
                            <span class="text-[10px] text-slate-400">pimpinan</span>
                        </div>
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-indigo-500/20 text-indigo-300">Executive</span>
                    </button>
                </div>
            </div> -->
        </div>

        <p class="text-center text-[11px] text-slate-500 mt-6">
            &copy; 2026 Bidang Persandian dan Keamanan Informasi, Diskominfo Kabupaten Ciamis.
        </p>
    </div>

    <script>
        function setLogin(email) {
            document.getElementById('login').value = email;
            document.getElementById('password').value = 'password';
        }
    </script>
    @if(config('services.recaptcha.site_key'))
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
    <script>
        document.getElementById('login-form').addEventListener('submit', function(e) {
            e.preventDefault();
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {action: 'login'}).then(function(token) {
                    document.getElementById('g-recaptcha-response').value = token;
                    document.getElementById('login-form').submit();
                });
            });
        });
    </script>
    @endif
</body>
</html>
