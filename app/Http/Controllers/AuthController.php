<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
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
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginField = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$loginField => $credentials['login'], 'password' => $credentials['password'], 'is_active' => true], $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $user->update(['last_login_at' => now()]);

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
