<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if ($request->isMethod('post')) {
            // Allow login via username or email
            $login = $request->input('login');
            $password = $request->input('password');

            if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
                $credentials = ['email' => $login, 'password' => $password];
            } else {
                $credentials = ['username' => $login, 'password' => $password];
            }

            if (auth()->attempt($credentials)) {
                return redirect()->route('admin.dashboard');
            }

            return back()->withErrors([
                'login' => 'The provided credentials do not match our records.',
            ]);
        }

        if(auth()->check()){
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    public function logout(Request $request)
    {
        auth()->logout();
        return redirect()->route('admin.login');
    }
}
