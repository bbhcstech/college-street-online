<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class CustomerAuthController extends Controller
{
    public function showLogin() { return view('pages.account-login'); }

    public function login(Request $request)
    {
        $creds = $request->validate(['email' => 'required|email', 'password' => 'required']);
        $key = 'login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['email' => 'Too many attempts. Try again later.']);
        }
        if (Auth::attempt($creds + ['role' => 'customer']) ) {
            $request->session()->regenerate(); // prevent session fixation, per FR-1
            RateLimiter::clear($key);
            return redirect()->intended(route('home'));
        }
        RateLimiter::hit($key, 300);
        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);
        $user = User::create($data + ['password' => Hash::make($data['password']), 'role' => 'customer']);
        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('home');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
