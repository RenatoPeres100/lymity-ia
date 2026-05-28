<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            return $user->isClientUser()
                ? redirect()->route('client.dashboard')
                : redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = \App\Models\User::where('email', $credentials['email'])->first();

        if ($user && $user->isAiEmployee()) {
            return back()->withErrors(['email' => 'Acesso não permitido para funcionários IA.'])->onlyInput('email');
        }

        if ($user && !$user->isActive()) {
            return back()->withErrors(['email' => 'Sua conta está inativa. Contate o administrador.'])->onlyInput('email');
        }

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'As credenciais estão incorretas.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();
        $user->update(['last_login_at' => now()]);

        $default = $user->isClientUser()
            ? route('client.dashboard')
            : route('admin.dashboard');

        return redirect()->intended($default);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
