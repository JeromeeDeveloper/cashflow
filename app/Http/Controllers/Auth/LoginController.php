<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            if (Auth::attempt($credentials, $request->filled('remember'))) {
                $request->session()->regenerate();

                // Set user status to active when they log in
                $user = Auth::user();
                DB::table('users')->where('id', $user->id)->update(['status' => 'active']);

                return $this->authenticated($request, $user);
            }

            // Check if user exists but password is wrong
            $user = \App\Models\User::where('email', $request->email)->first();

            if ($user) {
                return back()->withErrors([
                    'password' => 'The password you entered is incorrect. Please try again.',
                ])->onlyInput('email');
            } else {
                return back()->withErrors([
                    'email' => 'No account found with this email address. Please check your email or contact your administrator.',
                ])->onlyInput('email');
            }
        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'An error occurred during login. Please try again.',
            ])->onlyInput('email');
        }
    }

    protected function authenticated($request, $user)
    {
        // Update last login timestamp
        $user->updateLastLogin();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'head') {
            return redirect()->route('head.dashboard');
        } elseif ($user->role === 'branch') {
            return redirect()->route('branch.dashboard');
        } else {
            // Default fallback
            return redirect('/');
        }
    }

    public function logout(Request $request)
    {
        // Set user status to inactive when they log out
        if (Auth::check()) {
            $user = Auth::user();
            DB::table('users')->where('id', $user->id)->update(['status' => 'inactive']);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
