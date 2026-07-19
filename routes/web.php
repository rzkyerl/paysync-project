<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TeamController;
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

Route::get('/invite/{token}', [TeamController::class, 'showActivation'])->name('invite.show');
Route::post('/invite/{token}', [TeamController::class, 'activate'])->name('invite.activate');

// All app routes require authentication
Route::middleware('auth')->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

    // Dashboard routes — role validation in controller
    Route::get('/app/dashboard-hr', [DashboardController::class, 'show'])
        ->defaults('page', 'dashboard-hr')->middleware('role:super_admin,hr_manager')->name('dashboard.hr');
    Route::get('/app/dashboard-finance', [DashboardController::class, 'show'])
        ->defaults('page', 'dashboard-finance')->middleware('role:super_admin,finance_manager')->name('dashboard.finance');
    Route::get('/app/dashboard-employee', [DashboardController::class, 'show'])
        ->defaults('page', 'dashboard-employee')->middleware('role:super_admin,employee')->name('dashboard.employee');

    // Employee CRUD routes (hr_manager only — role check in controller)
    Route::get('/employees/import', [EmployeeController::class, 'import'])->middleware('role:hr_manager')->name('employees.import');
    Route::post('/employees/import', [EmployeeController::class, 'importStore'])->middleware('role:hr_manager')->name('employees.import.store');
    Route::get('/employees/import/template', [EmployeeController::class, 'downloadTemplate'])->middleware('role:hr_manager')->name('employees.import.template');
    Route::post('/employees/{employee}/verify-bank', [EmployeeController::class, 'verifyBank'])->middleware('role:hr_manager')->name('employees.verify-bank');
    Route::post('/employees/{employee}/reject-bank', [EmployeeController::class, 'rejectBank'])->middleware('role:hr_manager')->name('employees.reject-bank');
    Route::resource('employees', EmployeeController::class);

    // Payroll routes (hr_manager + finance_manager — role check in controller)
    Route::get('/app/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::get('/app/payroll/create', [PayrollController::class, 'create'])->middleware('role:hr_manager')->name('payroll.create');
    Route::post('/app/payroll', [PayrollController::class, 'storeNew'])->middleware('role:hr_manager')->name('payroll.store');
    Route::get('/app/payroll/attendance/template', [PayrollController::class, 'downloadAttendanceTemplate'])->name('payroll.attendance.template');
    Route::get('/app/payroll/{payroll}', [PayrollController::class, 'show'])->name('payroll.show');
    Route::get('/app/payroll/{payroll}/attendance', [PayrollController::class, 'importAttendance'])->middleware('role:hr_manager')->name('payroll.attendance.import');
    Route::post('/app/payroll/{payroll}/attendance', [PayrollController::class, 'storeAttendance'])->middleware('role:hr_manager')->name('payroll.attendance.store');
    Route::post('/app/payroll/{payroll}/calculate', [PayrollController::class, 'calculate'])->middleware('role:hr_manager')->name('payroll.calculate');
    Route::post('/app/payroll/{payroll}/anomaly/{item}/acknowledge', [PayrollController::class, 'acknowledgeAnomaly'])->middleware('role:hr_manager')->name('payroll.anomaly.acknowledge');
    Route::put('/payroll/{payroll}/recalculate', [PayrollController::class, 'store'])->name('payroll.recalculate');
    Route::put('/payroll/{payroll}/submit', [PayrollController::class, 'submit'])->name('payroll.submit');
    Route::post('/app/payroll/{payroll}/approve', [PayrollController::class, 'approve'])->middleware('role:finance_manager')->name('payroll.approve');
    Route::post('/app/payroll/{payroll}/reject', [PayrollController::class, 'reject'])->middleware('role:finance_manager')->name('payroll.reject');
    Route::post('/app/payroll/{payroll}/disburse', [PayrollController::class, 'disburse'])->middleware('role:finance_manager')->name('payroll.disburse');
    Route::get('/app/payroll/{payroll}/reconcile', [PayrollController::class, 'reconcile'])->name('payroll.reconcile');
    Route::get('/app/payroll/{payroll}/payslip/{employee}', [PayrollController::class, 'payslip'])->name('payroll.payslip');
    Route::get('/app/my-payslips', [PayrollController::class, 'myPayslips'])->middleware('role:employee')->name('payroll.my-payslips');

    Route::middleware('role:super_admin')->prefix('/app/settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::put('/company', [SettingsController::class, 'updateCompany'])->name('company.update');
        Route::put('/users/{user}/role', [SettingsController::class, 'updateUserRole'])->name('users.role');
        Route::delete('/users/{user}', [SettingsController::class, 'deactivateUser'])->name('users.deactivate');
    });

    Route::middleware('role:super_admin')->group(function () {
        Route::get('/app/team', [TeamController::class, 'index'])->name('team.index');
        Route::post('/app/team/invite', [TeamController::class, 'invite'])->name('team.invite');
        Route::post('/app/team/{user}/resend', [TeamController::class, 'resend'])->name('team.resend');
        Route::delete('/app/team/{user}', [TeamController::class, 'remove'])->name('team.remove');
    });

    // Catch-all for remaining app pages (attendance, approval, payslips, disbursement,
    // reconciliation, reports, settings, audit) — placed last so specific routes above take precedence
    Route::get('/app/{page?}', [DashboardController::class, 'show'])->name('app');
});
