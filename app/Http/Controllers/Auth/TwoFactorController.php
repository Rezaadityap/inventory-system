<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\TwoFactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    private const SESSION_USER = '2fa:user';

    private const SESSION_REMEMBER = '2fa:remember';

    /**
     * Show the OTP challenge screen.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge', [
            'email' => $this->maskEmail($user->email),
            'expiresInMinutes' => TwoFactor::codeTtlMinutes(),
        ]);
    }

    /**
     * Verify the submitted OTP and finish logging the user in.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $key = "otp-verify:{$user->id}";

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $request->session()->forget([self::SESSION_USER, self::SESSION_REMEMBER]);

            throw ValidationException::withMessages([
                'code' => 'Terlalu banyak percobaan. Silakan login ulang.',
            ]);
        }

        if (! TwoFactor::verifyCode($user, (string) $request->input('code'))) {
            RateLimiter::hit($key, 600);

            throw ValidationException::withMessages([
                'code' => 'Kode OTP salah atau sudah kedaluwarsa.',
            ]);
        }

        RateLimiter::clear($key);

        $remember = (bool) $request->session()->pull(self::SESSION_REMEMBER, false);
        $request->session()->forget(self::SESSION_USER);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        if ($request->boolean('trust_device')) {
            TwoFactor::trustDevice($user);
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Send a fresh OTP code.
     */
    public function resend(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        $key = "otp-resend:{$user->id}";

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'code' => "Tunggu {$seconds} detik sebelum meminta kode baru.",
            ]);
        }

        RateLimiter::hit($key, 600);
        TwoFactor::sendCode($user);

        return back()->with('status', 'Kode OTP baru sudah dikirim ke email kamu.');
    }

    private function pendingUser(Request $request): ?User
    {
        $userId = $request->session()->get(self::SESSION_USER);

        return $userId ? User::find($userId) : null;
    }

    private function maskEmail(string $email): string
    {
        [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');

        $visible = mb_substr($name, 0, min(2, mb_strlen($name)));
        $masked = $visible . str_repeat('*', max(3, mb_strlen($name) - mb_strlen($visible)));

        return $domain ? "{$masked}@{$domain}" : $masked;
    }
}
