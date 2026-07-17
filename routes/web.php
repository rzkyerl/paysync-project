<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('payflow.landing');
});

Route::view('/login', 'payflow.auth.login')->name('login');
Route::view('/register', 'payflow.auth.register')->name('register');
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
