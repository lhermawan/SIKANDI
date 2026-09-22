@extends('layouts.app')
@section('title', 'Security Settings (2FA)')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-800 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-white">Autentikasi Dua Langkah (2FA)</h2>
                <p class="text-sm text-slate-400 mt-1">Tambahkan keamanan ekstra ke akun Anda menggunakan Google Authenticator.</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-blue-500/10 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
        </div>

        <div class="p-6">
            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-rose-950/60 border border-rose-500/30 text-rose-300 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-950/60 border border-emerald-500/30 text-emerald-300 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if($user->two_factor_confirmed_at)
                <!-- 2FA is Enabled -->
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 mt-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-white">2FA Sudah Aktif</h3>
                        <p class="text-sm text-slate-400 mt-1 mb-6">Akun Anda saat ini dilindungi oleh Autentikasi Dua Langkah (TOTP). Setiap kali login, Anda akan diminta memasukkan kode 6 digit dari aplikasi authenticator Anda.</p>
                        
                        <form action="{{ route('profile.2fa.disable') }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan 2FA? Keamanan akun akan menurun.')">
                            @csrf
                            <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-sm font-semibold transition cursor-pointer shadow-lg shadow-rose-500/20">
                                Nonaktifkan 2FA
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <!-- 2FA is Disabled (Setup Phase) -->
                <div class="flex items-start gap-4 mb-8">
                    <div class="w-10 h-10 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center shrink-0 mt-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-white">2FA Belum Aktif</h3>
                        <p class="text-sm text-slate-400 mt-1">Untuk mengaktifkan, silakan ikuti langkah-langkah di bawah ini menggunakan aplikasi <strong>Google Authenticator</strong> atau <strong>Authy</strong>.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 border-t border-slate-800/80 pt-8">
                    <!-- Step 1: QR Code -->
                    <div class="space-y-4">
                        <h4 class="font-bold text-white flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center text-xs">1</span>
                            Scan QR Code
                        </h4>
                        <p class="text-sm text-slate-400">Buka aplikasi Authenticator Anda lalu pindai QR Code di bawah ini.</p>
                        
                        <div class="bg-white p-4 rounded-2xl inline-block shadow-lg">
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->generate($qrCodeUrl) !!}
                        </div>
                        
                        <p class="text-xs text-slate-500 mt-2">Atau masukkan kode manual:<br><code class="text-slate-300 font-mono tracking-widest">{{ $secret }}</code></p>
                    </div>

                    <!-- Step 2: Verification Form -->
                    <div class="space-y-4">
                        <h4 class="font-bold text-white flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center text-xs">2</span>
                            Verifikasi Kode OTP
                        </h4>
                        <p class="text-sm text-slate-400">Masukkan 6 digit angka yang muncul di aplikasi Authenticator untuk mengkonfirmasi pemasangan.</p>
                        
                        <form action="{{ route('profile.2fa.enable') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-slate-300 font-medium mb-1.5 text-sm">Kode OTP</label>
                                <input type="text" name="code" required autofocus autocomplete="off" placeholder="Contoh: 123456"
                                    class="w-full px-4 py-3 bg-slate-950/60 border border-slate-700/80 rounded-xl text-white text-lg tracking-[0.5em] text-center font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            </div>
                            
                            <button type="submit" class="w-full px-5 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-sm font-bold transition shadow-lg shadow-blue-500/20">
                                Aktifkan 2FA Sekarang
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
