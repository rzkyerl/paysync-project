<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests tambahan untuk User model helpers.
 * Melengkapi UserRoleTest.php yang sudah ada.
 */
class UserModelTest extends TestCase
{
    public function test_is_demo_user_returns_true_when_is_demo_is_true(): void
    {
        $user = new User(['is_demo' => true]);

        $this->assertTrue($user->isDemoUser());
    }

    public function test_is_demo_user_returns_false_when_is_demo_is_false(): void
    {
        $user = new User(['is_demo' => false]);

        $this->assertFalse($user->isDemoUser());
    }

    public function test_default_dashboard_for_unknown_role_returns_dashboard_hr(): void
    {
        $user = new User(['role' => 'unknown_role']);

        $this->assertSame('dashboard-hr', $user->defaultDashboard());
    }

    public function test_has_any_role_with_single_matching_role(): void
    {
        $user = new User(['role' => 'hr_manager']);

        $this->assertTrue($user->hasAnyRole(['hr_manager']));
    }

    public function test_has_any_role_returns_false_when_no_match(): void
    {
        $user = new User(['role' => 'employee']);

        $this->assertFalse($user->hasAnyRole(['hr_manager', 'finance_manager', 'super_admin']));
    }

    public function test_has_role_is_strict_comparison(): void
    {
        $user = new User(['role' => 'hr_manager']);

        $this->assertTrue($user->hasRole('hr_manager'));
        $this->assertFalse($user->hasRole('HR_MANAGER'));
        $this->assertFalse($user->hasRole('hr manager'));
    }

    public function test_is_super_admin_returns_false_for_non_super_admin(): void
    {
        foreach (['hr_manager', 'finance_manager', 'employee'] as $role) {
            $user = new User(['role' => $role]);
            $this->assertFalse($user->isSuperAdmin(), "Role {$role} should not be super_admin");
        }
    }

    public function test_is_hr_manager_returns_false_for_other_roles(): void
    {
        foreach (['super_admin', 'finance_manager', 'employee'] as $role) {
            $user = new User(['role' => $role]);
            $this->assertFalse($user->isHrManager(), "Role {$role} should not be hr_manager");
        }
    }

    public function test_is_finance_manager_returns_false_for_other_roles(): void
    {
        foreach (['super_admin', 'hr_manager', 'employee'] as $role) {
            $user = new User(['role' => $role]);
            $this->assertFalse($user->isFinanceManager(), "Role {$role} should not be finance_manager");
        }
    }

    public function test_is_employee_returns_false_for_other_roles(): void
    {
        foreach (['super_admin', 'hr_manager', 'finance_manager'] as $role) {
            $user = new User(['role' => $role]);
            $this->assertFalse($user->isEmployee(), "Role {$role} should not be employee");
        }
    }
}
