<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\Publisher;
use App\Models\User;

class PublisherAuthController extends Controller
{
    public function showLogin() { return view('publisher.login'); }

    public function showRegister() { return view('publisher.register'); }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'business_name' => 'required|string|max:200',
            'contact_details' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'publisher',
            ]);
            Publisher::create([
                'user_id' => $user->id,
                'business_name' => $data['business_name'],
                'contact_details' => $data['contact_details'] ?? null,
                'approval_status' => 'pending',
            ]);
        });

        return redirect()->route('publisher.login')->with('success', 'Application submitted. You can log in after admin approval.');
    }

    public function login(Request $request)
    {
        $creds = $request->validate(['email' => 'required|email', 'password' => 'required']);
        $key = 'publisher-login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['email' => 'Too many attempts. Try again later.']);
        }
        if (Auth::attempt($creds + ['role' => 'publisher'])) {
            if (Auth::user()->publisher?->approval_status !== 'approved') {
                $status = Auth::user()->publisher?->approval_status ?? 'pending';
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors(['email' => $status === 'rejected'
                    ? 'Your publisher application was rejected.'
                    : 'Your publisher application is waiting for admin approval.']);
            }
            $request->session()->regenerate();
            RateLimiter::clear($key);
            return redirect()->intended(route('publisher.dashboard'));
        }
        RateLimiter::hit($key, 300);
        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
