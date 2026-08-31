<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function afficher(): View|RedirectResponse
    {
        if (! config('cotiz.auth.enabled') || Auth::check()) {
            return redirect()->route('accueil');
        }

        return view('auth.login');
    }

    public function connecter(Request $request): RedirectResponse
    {
        $identifiants = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [], ['email' => 'adresse mail', 'password' => 'mot de passe']);

        if (! Auth::attempt($identifiants, true)) {
            return back()->withInput($request->only('email'))->withErrors(['email' => 'Identifiants incorrects.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('accueil'));
    }

    public function deconnecter(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
