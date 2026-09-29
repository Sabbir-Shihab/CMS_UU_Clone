<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function show()
    {
        if (session('is_admin')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($data['email'] === config('cms.admin_email') && Hash::check($data['password'], config('cms.admin_password_hash'))) {
            $request->session()->regenerate();
            $request->session()->put('is_admin', true);

            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['email' => 'Invalid admin email or password.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('is_admin');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
