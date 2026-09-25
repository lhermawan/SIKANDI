<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    /**
     * Tampilkan halaman setup 2FA di profil user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $google2fa = app('pragmarx.google2fa');

        $qrCodeUrl = null;
        $secret = $user->two_factor_secret;

        if (! $secret) {
            $secret = $google2fa->generateSecretKey();
            $request->session()->put('2fa_setup_secret', $secret);
        } else {
            $request->session()->forget('2fa_setup_secret');
        }

        if ($secret && ! $user->two_factor_confirmed_at) {
            $qrCodeUrl = $google2fa->getQRCodeUrl(
                config('app.name'),
                $user->email,
                $secret
            );
        }

        return view('profile.two-factor', compact('user', 'secret', 'qrCodeUrl'));
    }

    /**
     * Konfirmasi dan aktifkan 2FA.
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = Auth::user();
        $google2fa = app('pragmarx.google2fa');
        $secret = $request->session()->get('2fa_setup_secret', $user->two_factor_secret);

        if (! $secret) {
            return back()->with('error', 'Secret key tidak ditemukan. Silakan muat ulang halaman.');
        }

        $valid = $google2fa->verifyKey($secret, $request->code);

        if ($valid) {
            $user->update([
                'two_factor_secret' => $secret,
                'two_factor_confirmed_at' => now(),
            ]);
            $request->session()->forget('2fa_setup_secret');

            return back()->with('success', 'Two-Factor Authentication berhasil diaktifkan.');
        }

        return back()->with('error', 'Kode OTP tidak valid. Silakan coba lagi.');
    }

    /**
     * Nonaktifkan 2FA.
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = Auth::user();
        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        return back()->with('success', 'Two-Factor Authentication telah dinonaktifkan.');
    }

    /**
     * Tampilkan halaman input OTP setelah validasi password.
     */
    public function challenge(Request $request)
    {
        if (! $request->session()->has('2fa_user_id')) {
            return redirect()->route('login');
        }

        $userId = $request->session()->get('2fa_user_id');
        $user = User::find($userId);
        if ($user && $user->locked_until && now()->lt($user->locked_until)) {
            $request->session()->forget(['2fa_user_id', '2fa_remember']);
            $minutes = max(1, (int) now()->diffInMinutes($user->locked_until, false));

            return redirect()->route('login')->withErrors(['login' => "Akun Anda telah dikunci selama {$minutes} menit karena terlalu banyak percobaan gagal."]);
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * Verifikasi kode OTP dan login user.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $userId = $request->session()->get('2fa_user_id');
        if (! $userId) {
            return redirect()->route('login')->withErrors(['login' => 'Sesi login telah habis. Silakan login kembali.']);
        }

        $user = User::find($userId);
        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->locked_until && now()->lt($user->locked_until)) {
            $request->session()->forget(['2fa_user_id', '2fa_remember']);
            $minutes = max(1, (int) now()->diffInMinutes($user->locked_until, false));

            return redirect()->route('login')->withErrors(['login' => "⛔ Akun Anda telah dikunci selama {$minutes} menit karena terlalu banyak percobaan gagal."]);
        }

        $google2fa = app('pragmarx.google2fa');
        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if ($valid) {
            // Login sukses
            $remember = $request->session()->get('2fa_remember', false);
            Auth::login($user, $remember);

            $request->session()->regenerate();
            $request->session()->forget(['2fa_user_id', '2fa_remember']);

            $user->update([
                'last_login_at' => now(),
                'failed_login_count' => 0,
                'locked_until' => null,
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'action' => 'login_2fa',
                'module' => 'Authentication',
                'auditable_type' => get_class($user),
                'auditable_id' => $user->id,
                'record_name' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang kembali, '.$user->name);
        }

        $newCount = ($user->failed_login_count ?? 0) + 1;
        $lockedUntil = null;
        $shouldLock = false;

        if ($newCount >= 5) {
            $lockedUntil = now()->addMinutes(15);
            $shouldLock = true;
            $newCount = 0;
        }

        $user->update([
            'failed_login_count' => $newCount,
            'locked_until' => $lockedUntil,
        ]);

        if ($shouldLock) {
            $request->session()->forget(['2fa_user_id', '2fa_remember']);

            return redirect()->route('login')->withErrors([
                'login' => '⛔ Akun Anda telah dikunci selama 15 menit karena terlalu banyak percobaan OTP yang salah.',
            ]);
        }

        $remaining = 5 - $newCount;

        return back()->withErrors(['code' => "Kode OTP tidak valid atau sudah kadaluarsa. Sisa percobaan: {$remaining} kali sebelum akun dikunci."])->withInput();
    }
}
