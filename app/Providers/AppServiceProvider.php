<?php

namespace App\Providers;

use App\Models\Employee;
use App\Models\Payroll;
use App\Policies\EmployeePolicy;
use App\Policies\PayrollPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies (Laravel 11 style — no AuthServiceProvider needed)
        Gate::policy(Employee::class, EmployeePolicy::class);
        Gate::policy(Payroll::class, PayrollPolicy::class);

        View::composer('payflow.app', function ($view): void {
            $user = auth()->user();
            $notifications = $user
                ? $user->userNotifications()->latest()->limit(8)->get()
                : collect();

            $view->with([
                'headerNotifications' => $notifications,
                'unreadNotificationCount' => $user ? $user->unreadNotifications()->count() : 0,
            ]);
        });
    }
}
