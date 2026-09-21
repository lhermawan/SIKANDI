<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Maksimum percobaan login sebelum akun dikunci.
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * Durasi kunci akun dalam menit.
     */
    private const LOCKOUT_MINUTES = 15;

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function showLoginForm(): View|RedirectResponse
    {
        return $this->showLogin();
    }

    public function login(Request $request): RedirectResponse
    {
        $rules = [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'g-recaptcha-response' => ['required', 'string'],
        ];

        $credentials = $request->validate($rules, [
            'g-recaptcha-response.required' => 'Verifikasi reCAPTCHA diperlukan. Pastikan JavaScript diaktifkan di browser Anda.',
        ]);

        // --- 1. RATE LIMITING (by IP) ---
        $throttleKey = 'login.'.Str::lower($request->input('login')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'login' => "Terlalu banyak percobaan login dari IP ini. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('login');
        }

        // --- VALIDASI RECAPTCHA V3 ---
        $recaptchaResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret_key'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ]);

        $recaptchaData = $recaptchaResponse->json();
        $recaptchaScore = $recaptchaData['score'] ?? 0;

        if (! $recaptchaResponse->successful() || ! ($recaptchaData['success'] ?? false) || $recaptchaScore < 0.5) {
            // Log percobaan dengan skor rendah (kemungkinan bot)
            Log::warning('reCAPTCHA failed on login', [
                'ip' => $request->ip(),
                'login' => $request->input('login'),
                'score' => $recaptchaScore,
                'errors' => $recaptchaData['error-codes'] ?? [],
            ]);
            RateLimiter::hit($throttleKey, 60);

            return back()->withErrors([
                'login' => 'Verifikasi keamanan reCAPTCHA gagal (skor: '.round($recaptchaScore, 2).'). Silakan coba lagi.',
            ])->onlyInput('login');
        }

        // --- 3. IDENTIFIKASI USER SEBELUM AUTH::ATTEMPT ---
        $loginField = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user = User::where($loginField, $credentials['login'])->first();

        // --- 4. CEK ACCOUNT LOCKOUT (by DB record) ---
        if ($user && $user->locked_until && now()->lt($user->locked_until)) {
            $minutesLeft = (int) now()->diffInMinutes($user->locked_until, false);
            $minutesLeft = max(1, $minutesLeft);

            // Log percobaan saat terkunci
            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'action' => 'login_blocked',
                'module' => 'Authentication',
                'auditable_type' => get_class($user),
                'auditable_id' => $user->id,
                'record_name' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => ['reason' => 'Account locked'],
            ]);

            return back()->withErrors([
                'login' => "⛔ Akun Anda dikunci sementara karena terlalu banyak percobaan login gagal. Silakan coba lagi dalam {$minutesLeft} menit.",
            ])->onlyInput('login');
        }

        // --- 5. AUTH ATTEMPT ---
        if (Auth::attempt([$loginField => $credentials['login'], 'password' => $credentials['password'], 'is_active' => true], $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Reset semua penanda keamanan setelah login berhasil
            RateLimiter::clear($throttleKey);

            $user = Auth::user();
            $user->update([
                'last_login_at' => now(),
                'failed_login_count' => 0,
                'locked_until' => null,
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'action' => 'login',
                'module' => 'Authentication',
                'auditable_type' => get_class($user),
                'auditable_id' => $user->id,
                'record_name' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang kembali, '.$user->name);
        }

        // --- 6. LOGIN GAGAL: INCREMENT COUNTER & LOCKOUT ---
        RateLimiter::hit($throttleKey, 60); // 60 detik window per IP

        if ($user) {
            $newCount = $user->failed_login_count + 1;
            $lockedUntil = null;
            $shouldLock = false;

            if ($newCount >= self::MAX_ATTEMPTS) {
                $lockedUntil = now()->addMinutes(self::LOCKOUT_MINUTES);
                $shouldLock = true;
                $newCount = 0; // Reset counter setelah dikunci
            }

            $user->update([
                'failed_login_count' => $newCount,
                'locked_until' => $lockedUntil,
            ]);

            // Log percobaan gagal
            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'action' => 'login_failed',
                'module' => 'Authentication',
                'auditable_type' => get_class($user),
                'auditable_id' => $user->id,
                'record_name' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => ['attempt' => $newCount, 'locked' => $shouldLock],
            ]);

            if ($shouldLock) {
                return back()->withErrors([
                    'login' => '⛔ Akun Anda telah dikunci selama '.self::LOCKOUT_MINUTES.' menit karena terlalu banyak percobaan login gagal.',
                ])->onlyInput('login');
            }

            $remaining = self::MAX_ATTEMPTS - $newCount;

            return back()->withErrors([
                'login' => "Kombinasi username/email dan password salah. Sisa percobaan: {$remaining} kali sebelum akun dikunci.",
            ])->onlyInput('login');
        }

        return back()->withErrors([
            'login' => 'Kombinasi email/username dan password tidak cocok atau akun Anda tidak aktif.',
        ])->onlyInput('login');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'action' => 'logout',
                'module' => 'Authentication',
                'auditable_type' => get_class($user),
                'auditable_id' => $user->id,
                'record_name' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah berhasil keluar dari sistem.');
    }
}
