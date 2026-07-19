<?php

namespace Tests\Unit;

use App\Models\Employee;
use App\Models\User;
use App\Policies\EmployeePolicy;
use PHPUnit\Framework\TestCase;

class EmployeePolicyTest extends TestCase
{
    public function test_super_admin_can_view_only_employees_in_the_same_company(): void
    {
        $policy = new EmployeePolicy;
        $admin = new User(['role' => 'super_admin', 'company_id' => 10]);
        $sameCompany = new Employee(['id' => 1, 'company_id' => 10]);
        $otherCompany = new Employee(['id' => 2, 'company_id' => 20]);

        $this->assertTrue($policy->view($admin, $sameCompany));
        $this->assertFalse($policy->view($admin, $otherCompany));
        $this->assertFalse($policy->create($admin));
        $this->assertFalse($policy->update($admin, $sameCompany));
        $this->assertFalse($policy->delete($admin, $sameCompany));
    }

    public function test_hr_manager_can_crud_employees_in_the_same_company_only(): void
    {
        $policy = new EmployeePolicy;
        $hr = new User(['role' => 'hr_manager', 'company_id' => 10]);
        $sameCompany = new Employee(['id' => 1, 'company_id' => 10]);
        $otherCompany = new Employee(['id' => 2, 'company_id' => 20]);

        $this->assertTrue($policy->create($hr));
        $this->assertTrue($policy->view($hr, $sameCompany));
        $this->assertTrue($policy->update($hr, $sameCompany));
        $this->assertTrue($policy->delete($hr, $sameCompany));
        $this->assertFalse($policy->view($hr, $otherCompany));
        $this->assertFalse($policy->update($hr, $otherCompany));
    }
}
