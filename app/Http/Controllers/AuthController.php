<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = $request->user();
            if ($user->status !== UserStatus::Active) {
                Auth::logout();

                return back()->withErrors([
                    'email' => 'Ce compte est actuellement suspendu ou en attente de validation.',
                ]);
            }

            $user->update(['last_login_at' => now()]);

            if ($user->isAdminOrEditor()) {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended(route('magazine.home'));
        }

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('magazine.home');
    }
}
