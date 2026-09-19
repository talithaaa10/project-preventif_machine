<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LoginBasic extends Controller
{
    public function index()
    {
        return view('content.authentications.auth-login-basic');
    }
    public function authenticate(Request $request)
    {
        $request->validate([
            'email-username' => 'required',
            'password'       => 'required'
        ]);

        $username = $request->input('email-username');
        $password = $request->input('password');
        $user = User::where('name', $username)->first();

        if ($user && (\Illuminate\Support\Facades\Hash::check($password, $user->password) || $user->password === $password)) {
            Auth::login($user);
            session([
                'user_name' => $user->name,
                'user_role' => $user->role,
            ]);

            return redirect('/dashboard')->with('success', 'Selamat datang kembali, ' . $user->name);
        }
        return back()->withErrors([
            'loginError' => 'Username atau Password salah!'
        ])->withInput();
    }

        public function logout(Request $request)
    {
        // 1. Keluar dari session Auth
        Auth::logout();

        // 2. Hapus semua data session (termasuk role)
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 3. Kembalikan ke halaman login
        return redirect('/')->with('success', 'Anda telah berhasil keluar.');
    }

}