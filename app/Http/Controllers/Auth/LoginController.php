<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !$user->is_active) {
            return back()->withErrors(['username' => 'Akun tidak ditemukan atau tidak aktif.'])->withInput();
        }

        if (!Auth::attempt(['username' => $request->username, 'password' => $request->password], $request->boolean('remember'))) {
            return back()->withErrors(['username' => 'Username atau password salah.'])->withInput();
        }

        $user->update(['last_login_at' => now()]);
        $request->session()->regenerate();

        // Set active hospital — ambil hospital pertama yang aktif
        $firstHospital = $user->hospitals()
                      ->wherePivot('is_active', true)
                      ->where('hospitals.is_active', true)
                      ->first();
                      
        if ($firstHospital) {
            session(['active_hospital_id' => $firstHospital->id]);
        }

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}