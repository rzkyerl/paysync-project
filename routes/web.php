<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('payflow.landing');
});

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.attempt');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

Route::view('/verify', 'payflow.auth.verify')->name('verify');
Route::view('/onboarding', 'payflow.onboarding')->name('onboarding');

Route::get('/app/{page?}', function (?string $page = 'dashboard-hr') {
    $allowed = [
        'dashboard-hr',
        'dashboard-finance',
        'dashboard-employee',
        'employees',
        'attendance',
        'payroll',
        'approval',
        'payslips',
        'disbursement',
        'reconciliation',
        'reports',
        'settings',
        'audit',
    ];

    abort_unless(in_array($page, $allowed, true), 404);

    return view('payflow.app', ['page' => $page]);
})->name('app');
