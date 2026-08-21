<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AdminAuthController extends Controller
{
    public function showLogin() { return view('admin.login'); }

    public function login(Request $request)
    {
        $creds = $request->validate(['email' => 'required|email', 'password' => 'required']);
        $key = 'admin-login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['email' => 'Too many attempts. Try again later.']);
        }
        if (Auth::attempt($creds + ['role' => 'admin'])) {
            $request->session()->regenerate();
            RateLimiter::clear($key);
            return redirect()->intended(route('admin.dashboard'));
        }
        RateLimiter::hit($key, 300);
        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
