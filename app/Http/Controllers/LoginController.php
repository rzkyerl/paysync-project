<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return $this->redirectForUser(Auth::user());
        }

        return view('payflow.auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt(array_merge($credentials, ['status' => 'active']), $remember)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password yang kamu masukkan salah.',
            ]);
        }

        $request->session()->regenerate();

        return $this->redirectForUser($request->user());
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    private function redirectForUser(User $user)
    {
        if ($user->company_id === null && ! $user->isDemoUser()) {
            return redirect()->route('onboarding');
        }

        return redirect()->to('/app/'.$user->defaultDashboard());
    }
}
