<?php

namespace Tests\Unit;

use App\Models\User;
use Carbon\CarbonInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    public static function roles(): array
    {
        return [
            'super admin' => ['super_admin', 'isSuperAdmin', 'dashboard-hr'],
            'HR manager' => ['hr_manager', 'isHrManager', 'dashboard-hr'],
            'finance manager' => ['finance_manager', 'isFinanceManager', 'dashboard-finance'],
            'employee' => ['employee', 'isEmployee', 'dashboard-employee'],
        ];
    }

    #[DataProvider('roles')]
    public function test_role_helpers_and_default_dashboard(string $role, string $roleHelper, string $dashboard): void
    {
        $user = new User(['role' => $role]);

        $this->assertTrue($user->{$roleHelper}());
        $this->assertTrue($user->hasRole($role));
        $this->assertTrue($user->hasAnyRole(['another_role', $role]));
        $this->assertFalse($user->hasRole('another_role'));
        $this->assertFalse($user->hasAnyRole(['another_role']));
        $this->assertSame($dashboard, $user->defaultDashboard());
    }

    public function test_invitation_expiry_is_cast_to_datetime(): void
    {
        $user = new User([
            'status' => 'invited',
            'invitation_token' => 'example-token',
            'invitation_expires_at' => '2026-07-26 12:00:00',
        ]);

        $this->assertSame('invited', $user->status);
        $this->assertSame('example-token', $user->invitation_token);
        $this->assertInstanceOf(CarbonInterface::class, $user->invitation_expires_at);
    }
}
