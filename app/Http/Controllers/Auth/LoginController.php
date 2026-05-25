<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Attempt authentication
        if (!Auth::attempt($credentials, $request->filled('remember'))) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
                ]);
        }

        // Regenerate session
        Session::regenerate();

        return redirect()->intended(route('home'))
            ->with('success', 'Connecté avec succès!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();

        return redirect()->route('home');
    }
}
