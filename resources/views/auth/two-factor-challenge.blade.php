<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Keamanan (2FA) — SIKANDI</title>
    @vite(['resources/css/app.css'])
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-950 min-h-screen flex items-center justify-center p-4 antialiased relative overflow-hidden">
    
    <!-- Background Decor -->
    <div class="absolute inset-0 z-0 opacity-20">
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-600 rounded-full mix-blend-screen filter blur-[100px] animate-pulse"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-indigo-600 rounded-full mix-blend-screen filter blur-[100px] animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-slate-900 border border-slate-700/80 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl shadow-blue-900/20">
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Verifikasi 2 Langkah</h1>
            <p class="text-slate-400 mt-2 text-sm">Buka aplikasi authenticator Anda untuk melihat kode akses SIKANDI.</p>
        </div>

        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-800/80 rounded-3xl p-8 shadow-2xl">
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-rose-950/60 border border-rose-500/30 text-rose-300 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('2fa.verify') }}" method="POST" class="space-y-6">
                @csrf
                
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-2">Kode OTP (6 Digit)</label>
                    <input type="text" name="code" required autofocus autocomplete="one-time-code" pattern="[0-9]*" inputmode="numeric"
                        class="w-full px-5 py-4 bg-slate-950/60 border border-slate-700 rounded-xl text-white text-2xl tracking-[0.5em] text-center font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-inner">
                </div>
                
                <button type="submit" class="w-full py-3.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-bold transition shadow-lg shadow-blue-500/30 ring-1 ring-blue-500/50">
                    Verifikasi Login
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm text-slate-500 hover:text-slate-300 transition">Kembali ke halaman login</a>
            </div>
        </div>
    </div>
</body>
</html>
