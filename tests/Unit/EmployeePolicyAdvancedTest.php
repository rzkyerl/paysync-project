<?php

namespace Tests\Unit;

use App\Models\Employee;
use App\Models\User;
use App\Policies\EmployeePolicy;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests tambahan untuk EmployeePolicy.
 * Melengkapi EmployeePolicyTest.php yang sudah ada.
 */
class EmployeePolicyAdvancedTest extends TestCase
{
    private EmployeePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new EmployeePolicy();
    }

    // -----------------------------------------------------------------------
    // Employee role — self-access
    // -----------------------------------------------------------------------

    public function test_employee_can_view_own_linked_record(): void
    {
        $employee = new Employee(['id' => 55, 'company_id' => 10]);
        $employee->id = 55; // set explicitly since constructor may not persist to id
        $user     = new User([
            'role'        => 'employee',
            'company_id'  => 10,
            'employee_id' => 55,
        ]);

        $this->assertTrue($this->policy->view($user, $employee));
    }

    public function test_employee_cannot_view_another_employees_record(): void
    {
        $ownEmployee   = new Employee(['company_id' => 10]);
        $ownEmployee->id = 55;
        $otherEmployee = new Employee(['company_id' => 10]);
        $otherEmployee->id = 99;
        $user          = new User([
            'role'        => 'employee',
            'company_id'  => 10,
            'employee_id' => 55,
        ]);

        $this->assertTrue($this->policy->view($user, $ownEmployee));
        $this->assertFalse($this->policy->view($user, $otherEmployee));
    }

    public function test_employee_without_employee_id_cannot_view_any_record(): void
    {
        $employee = new Employee(['id' => 55, 'company_id' => 10]);
        $user     = new User([
            'role'        => 'employee',
            'company_id'  => 10,
            'employee_id' => null,
        ]);

        $this->assertFalse($this->policy->view($user, $employee));
    }

    public function test_employee_in_different_company_cannot_view_record(): void
    {
        $employee = new Employee(['id' => 55, 'company_id' => 10]);
        $user     = new User([
            'role'        => 'employee',
            'company_id'  => 20,  // berbeda company
            'employee_id' => 55,
        ]);

        $this->assertFalse($this->policy->view($user, $employee));
    }

    // -----------------------------------------------------------------------
    // Null company_id for manager
    // -----------------------------------------------------------------------

    public function test_manager_with_null_company_id_cannot_view(): void
    {
        $employee = new Employee(['id' => 1, 'company_id' => 10]);
        $hr       = new User(['role' => 'hr_manager', 'company_id' => null]);

        $this->assertFalse($this->policy->view($hr, $employee));
    }

    public function test_manager_with_null_company_id_cannot_update(): void
    {
        $employee = new Employee(['id' => 1, 'company_id' => 10]);
        $hr       = new User(['role' => 'hr_manager', 'company_id' => null]);

        $this->assertFalse($this->policy->update($hr, $employee));
    }

    // -----------------------------------------------------------------------
    // Finance manager — read only, no create/update/delete
    // -----------------------------------------------------------------------

    public function test_finance_manager_can_view_same_company_employee(): void
    {
        $employee = new Employee(['id' => 1, 'company_id' => 10]);
        $finance  = new User(['role' => 'finance_manager', 'company_id' => 10]);

        $this->assertTrue($this->policy->view($finance, $employee));
    }

    public function test_finance_manager_cannot_create_employees(): void
    {
        $finance = new User(['role' => 'finance_manager', 'company_id' => 10]);

        $this->assertFalse($this->policy->create($finance));
    }

    public function test_finance_manager_cannot_update_employees(): void
    {
        $employee = new Employee(['id' => 1, 'company_id' => 10]);
        $finance  = new User(['role' => 'finance_manager', 'company_id' => 10]);

        $this->assertFalse($this->policy->update($finance, $employee));
    }

    public function test_finance_manager_cannot_delete_employees(): void
    {
        $employee = new Employee(['id' => 1, 'company_id' => 10]);
        $finance  = new User(['role' => 'finance_manager', 'company_id' => 10]);

        $this->assertFalse($this->policy->delete($finance, $employee));
    }

    // -----------------------------------------------------------------------
    // delete delegates to update logic
    // -----------------------------------------------------------------------

    public function test_delete_requires_same_company_as_update(): void
    {
        $sameCompany  = new Employee(['id' => 1, 'company_id' => 10]);
        $otherCompany = new Employee(['id' => 2, 'company_id' => 20]);
        $hr           = new User(['role' => 'hr_manager', 'company_id' => 10]);

        $this->assertTrue($this->policy->delete($hr, $sameCompany));
        $this->assertFalse($this->policy->delete($hr, $otherCompany));
    }
}
