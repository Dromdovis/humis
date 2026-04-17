<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session('authenticated')) {
            return redirect()->route('vacations.index');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ], [
            'password.required' => 'Įveskite slaptažodį.',
        ]);

        if ($request->password === config('app.humis_password')) {
            $request->session()->regenerate();
            session(['authenticated' => true]);

            return redirect()->route('vacations.index');
        }

        return back()->withErrors([
            'password' => 'Neteisingas slaptažodis.',
        ]);
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Sėkmingai atsijungėte.');
    }
}
