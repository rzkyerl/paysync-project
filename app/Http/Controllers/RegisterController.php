<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return redirect('/app/dashboard-hr');
        }

        return view('payflow.auth.register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'email'            => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone'            => ['nullable', 'string', 'max:20'],
            'position'         => ['nullable', 'string', 'max:100'],
            'company'          => ['required', 'string', 'max:150'],
            'company_size'     => ['required', 'string'],
            'industry'         => ['nullable', 'string', 'max:100'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
            'terms'            => ['accepted'],
        ], [
            'name.required'         => 'Nama lengkap wajib diisi.',
            'email.required'        => 'Email kerja wajib diisi.',
            'email.email'           => 'Format email tidak valid.',
            'email.unique'          => 'Email ini sudah terdaftar. Silakan masuk.',
            'company.required'      => 'Nama perusahaan wajib diisi.',
            'company_size.required' => 'Jumlah karyawan wajib dipilih.',
            'password.required'     => 'Password wajib diisi.',
            'password.min'          => 'Password minimal 8 karakter.',
            'password.confirmed'    => 'Konfirmasi password tidak cocok.',
            'terms.accepted'        => 'Anda harus menyetujui Ketentuan Penggunaan.',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect('/app/dashboard-hr');
    }
}
