<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 60;

    public function showLoginForm(): View
    {
        return view('admin.auth.login');
    }

    /**
     * Sign in with email and password. Failed attempts are limited per email and IP.
     * Unknown email, wrong password and a deleted account all return the same message.
     */
    public function login(LoginRequest $request): RedirectResponse|Response
    {
        $email = Str::lower($request->validated('email'));
        $throttleKey = $this->throttleKey($email, (string) $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            return $this->throttledResponse($email, RateLimiter::availableIn($throttleKey));
        }

        $credentials = [
            'email' => $email,
            'password' => $request->validated('password'),
            'del_status' => fn ($query) => $query->where(
                fn ($inner) => $inner->whereNull('del_status')->orWhere('del_status', 0)
            ),
        ];

        if (! Auth::attempt($credentials)) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            return back()
                ->withInput(['email' => $email])
                ->withErrors(['email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง']);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();
        $this->startSingleSession($request);

        return redirect()->intended('/admin');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->user()?->forceFill(['_token' => null])->save();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Store a fresh token on the user and in this session. EnsureSingleSession compares
     * the two, so signing in on another device replaces the token and ends this session.
     */
    private function startSingleSession(Request $request): void
    {
        $sessionToken = Str::random(60);

        $now = now();
        $request->user()->forceFill([
            '_token' => $sessionToken,
            'lastvisit_at' => $now,
            'last_activity' => $now,
        ])->save();

        $request->session()->put('session_token', $sessionToken);
        $request->session()->put('last_activity', time());
    }

    private function throttleKey(string $email, string $ip): string
    {
        return Str::transliterate($email.'|'.$ip);
    }

    private function throttledResponse(string $email, int $seconds): Response
    {
        $view = view('admin.auth.login', ['email' => $email])
            ->withErrors(['email' => 'พยายามเข้าสู่ระบบหลายครั้งเกินไป กรุณาลองใหม่ใน :seconds วินาที', ['seconds' => $seconds]]);

        return response($view, 429);
    }
}
