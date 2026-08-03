<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests untuk GlobalSearchController.
 */
class GlobalSearchControllerTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $hrManager;
    private User $financeManager;
    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create(['name' => 'Acme Corp']);

        $this->hrManager = User::factory()->create([
            'role'       => 'hr_manager',
            'status'     => 'active',
            'company_id' => $this->company->id,
        ]);

        $this->financeManager = User::factory()->create([
            'role'       => 'finance_manager',
            'status'     => 'active',
            'company_id' => $this->company->id,
        ]);

        $this->employee = User::factory()->create([
            'role'       => 'employee',
            'status'     => 'active',
            'company_id' => $this->company->id,
        ]);
    }

    // -----------------------------------------------------------------------
    // Query too short
    // -----------------------------------------------------------------------

    public function test_query_less_than_2_chars_returns_empty_results(): void
    {
        $this->actingAs($this->hrManager)
            ->getJson(route('app.search', ['q' => 'a']))
            ->assertOk()
            ->assertJson([
                'modules'   => [],
                'employees' => [],
            ]);
    }

    public function test_empty_query_returns_empty_results(): void
    {
        $this->actingAs($this->hrManager)
            ->getJson(route('app.search', ['q' => '']))
            ->assertOk()
            ->assertJson([
                'modules'   => [],
                'employees' => [],
            ]);
    }

    // -----------------------------------------------------------------------
    // Module search — role scoping
    // -----------------------------------------------------------------------

    public function test_hr_manager_can_search_dashboard_hr_module(): void
    {
        $response = $this->actingAs($this->hrManager)
            ->getJson(route('app.search', ['q' => 'Dashboard']))
            ->assertOk();

        $modules = $response->json('modules');
        $labels  = array_column($modules, 'label');
        $this->assertContains('Dashboard HR', $labels);
    }

    public function test_finance_manager_does_not_see_hr_only_modules(): void
    {
        // finance_manager tidak memiliki akses ke 'payroll' module (hr_manager only)
        // tapi bisa lihat 'Daftar Karyawan' karena finance_manager ada di roles-nya
        // Yang tidak bisa dilihat finance_manager: Proses Payroll (roles: super_admin, hr_manager)
        $response = $this->actingAs($this->financeManager)
            ->getJson(route('app.search', ['q' => 'Proses']))
            ->assertOk();

        $modules = $response->json('modules');
        $labels  = array_column($modules, 'label');
        // 'Proses Payroll' hanya untuk super_admin dan hr_manager
        $this->assertNotContains('Proses Payroll', $labels);
    }

    public function test_finance_manager_sees_approval_module(): void
    {
        $response = $this->actingAs($this->financeManager)
            ->getJson(route('app.search', ['q' => 'Approval']))
            ->assertOk();

        $modules = $response->json('modules');
        $labels  = array_column($modules, 'label');
        $this->assertContains('Approval Queue', $labels);
    }

    public function test_super_admin_sees_settings_module(): void
    {
        $superAdmin = User::factory()->create([
            'role'       => 'super_admin',
            'status'     => 'active',
            'company_id' => $this->company->id,
        ]);

        $response = $this->actingAs($superAdmin)
            ->getJson(route('app.search', ['q' => 'Pengaturan']))
            ->assertOk();

        $modules = $response->json('modules');
        $labels  = array_column($modules, 'label');
        $this->assertContains('Pengaturan Perusahaan', $labels);
    }

    // -----------------------------------------------------------------------
    // Employee search — scoped to company
    // -----------------------------------------------------------------------

    public function test_hr_manager_can_search_employees_by_name(): void
    {
        Employee::create([
            'company_id'   => $this->company->id,
            'nip'          => 'EMP-0001',
            'name'         => 'Budi Santoso',
            'department'   => 'IT',
            'position'     => 'Dev',
            'work_status'  => 'active',
            'join_date'    => '2022-01-01',
            'basic_salary' => 5_000_000,
        ]);

        $response = $this->actingAs($this->hrManager)
            ->getJson(route('app.search', ['q' => 'Budi']))
            ->assertOk();

        $employees = $response->json('employees');
        $this->assertCount(1, $employees);
        $this->assertSame('Budi Santoso', $employees[0]['name']);
    }

    public function test_hr_manager_cannot_see_employees_from_other_company(): void
    {
        $otherCompany = Company::create(['name' => 'Other Corp']);
        Employee::create([
            'company_id'   => $otherCompany->id,
            'nip'          => 'EMP-9999',
            'name'         => 'Other Employee',
            'department'   => 'IT',
            'position'     => 'Dev',
            'work_status'  => 'active',
            'join_date'    => '2022-01-01',
            'basic_salary' => 5_000_000,
        ]);

        $response = $this->actingAs($this->hrManager)
            ->getJson(route('app.search', ['q' => 'Other Employee']))
            ->assertOk();

        $employees = $response->json('employees');
        $this->assertCount(0, $employees);
    }

    public function test_employee_role_does_not_get_employee_search_results(): void
    {
        Employee::create([
            'company_id'   => $this->company->id,
            'nip'          => 'EMP-0001',
            'name'         => 'Budi Santoso',
            'department'   => 'IT',
            'position'     => 'Dev',
            'work_status'  => 'active',
            'join_date'    => '2022-01-01',
            'basic_salary' => 5_000_000,
        ]);

        $response = $this->actingAs($this->employee)
            ->getJson(route('app.search', ['q' => 'Budi']))
            ->assertOk();

        $employees = $response->json('employees');
        $this->assertCount(0, $employees);
    }

    // -----------------------------------------------------------------------
    // Results limits
    // -----------------------------------------------------------------------

    public function test_modules_results_capped_at_5(): void
    {
        // Query yang cocok dengan banyak modul
        $response = $this->actingAs($this->hrManager)
            ->getJson(route('app.search', ['q' => 'da']))
            ->assertOk();

        $modules = $response->json('modules');
        $this->assertLessThanOrEqual(5, count($modules));
    }

    public function test_employee_results_capped_at_6(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Employee::create([
                'company_id'   => $this->company->id,
                'nip'          => 'EMP-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'name'         => 'Budi Person ' . $i,
                'department'   => 'IT',
                'position'     => 'Dev',
                'work_status'  => 'active',
                'join_date'    => '2022-01-01',
                'basic_salary' => 5_000_000,
            ]);
        }

        $response = $this->actingAs($this->hrManager)
            ->getJson(route('app.search', ['q' => 'Budi']))
            ->assertOk();

        $employees = $response->json('employees');
        $this->assertLessThanOrEqual(6, count($employees));
    }
}
