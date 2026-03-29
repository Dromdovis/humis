<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Rodyti login formą
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Patikrinti email ir nustatyti ar naujas ar grįžtantis
     */
    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $employee = Employee::where('email', $request->email)
            ->where('is_active', true)
            ->first();

        if (!$employee) {
            return response()->json([
                'exists' => false,
                'message' => 'Šis el. paštas nerastas sistemoje. Kreipkitės į administratorių.',
            ]);
        }

        return response()->json([
            'exists' => true,
            'is_registered' => $employee->isRegistered(),
            'name' => $employee->name,
        ]);
    }

    /**
     * Prisijungti (grįžtantis vartotojas)
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $employee = Employee::where('email', $credentials['email'])
            ->where('is_active', true)
            ->first();

        if (!$employee) {
            return back()->withErrors([
                'email' => 'Šis el. paštas nerastas sistemoje.',
            ])->onlyInput('email');
        }

        if (!$employee->isRegistered()) {
            return back()->withErrors([
                'email' => 'Jūs dar neužsiregistravote. Sukurkite slaptažodį.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'))
                ->with('success', 'Sveiki, ' . $employee->name . '!');
        }

        return back()->withErrors([
            'password' => 'Neteisingas slaptažodis.',
        ])->onlyInput('email');
    }

    /**
     * Registruotis (pirmas kartas - sukurti slaptažodį)
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $employee = Employee::where('email', $validated['email'])
            ->where('is_active', true)
            ->first();

        if (!$employee) {
            return back()->withErrors([
                'email' => 'Šis el. paštas nerastas sistemoje.',
            ]);
        }

        if ($employee->isRegistered()) {
            return back()->withErrors([
                'email' => 'Šis vartotojas jau užsiregistravęs. Prisijunkite.',
            ]);
        }

        $employee->update([
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($employee, true);

        return redirect()->route('dashboard')
            ->with('success', 'Sveiki, ' . $employee->name . '! Paskyra sukurta sėkmingai.');
    }

    /**
     * Atsijungti
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Sėkmingai atsijungėte.');
    }
}
