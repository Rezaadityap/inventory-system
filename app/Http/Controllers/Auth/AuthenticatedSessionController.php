<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\TwoFactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();

        // Role tertentu (admin, pelanggan) wajib OTP kecuali perangkatnya sudah dipercaya.
        if (TwoFactor::required($user) && ! TwoFactor::deviceTrusted($user)) {
            $remember = $request->boolean('remember');

            Auth::guard('web')->logout();
            $request->session()->regenerate();
            $request->session()->put('2fa:user', $user->id);
            $request->session()->put('2fa:remember', $remember);

            TwoFactor::sendCode($user);

            return redirect()->route('two-factor.notice');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
