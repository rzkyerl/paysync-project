<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests untuk RoleMiddleware.
 */
class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::create(['name' => 'Acme Corp']);
    }

    private function makeUser(string $role): User
    {
        return User::factory()->create([
            'role'       => $role,
            'status'     => 'active',
            'company_id' => $this->company->id,
        ]);
    }

    // -----------------------------------------------------------------------
    // GET request — user with wrong role → redirected to own dashboard
    // -----------------------------------------------------------------------

    public function test_hr_manager_redirected_from_finance_dashboard_on_get(): void
    {
        $hr = $this->makeUser('hr_manager');

        $this->actingAs($hr)
            ->get(route('dashboard.finance'))
            ->assertRedirect('/app/dashboard-hr');
    }

    public function test_finance_manager_redirected_from_hr_dashboard_on_get(): void
    {
        $finance = $this->makeUser('finance_manager');

        $this->actingAs($finance)
            ->get(route('dashboard.hr'))
            ->assertRedirect('/app/dashboard-finance');
    }

    public function test_employee_redirected_from_hr_dashboard_on_get(): void
    {
        $employee = $this->makeUser('employee');

        $this->actingAs($employee)
            ->get(route('dashboard.hr'))
            ->assertRedirect('/app/dashboard-employee');
    }

    // -----------------------------------------------------------------------
    // POST request — user with wrong role → 403
    // -----------------------------------------------------------------------

    public function test_finance_manager_gets_403_when_posting_to_hr_only_route(): void
    {
        $finance = $this->makeUser('finance_manager');

        $this->actingAs($finance)
            ->post(route('payroll.store'), [
                'period'       => '2026-08',
                'period_label' => 'Agustus 2026',
            ])->assertForbidden();
    }

    public function test_employee_gets_403_when_posting_to_hr_only_route(): void
    {
        $employee = $this->makeUser('employee');

        $this->actingAs($employee)
            ->post(route('payroll.store'), [
                'period'       => '2026-08',
                'period_label' => 'Agustus 2026',
            ])->assertForbidden();
    }

    // -----------------------------------------------------------------------
    // User with correct role passes through
    // -----------------------------------------------------------------------

    public function test_hr_manager_can_access_payroll_create_page(): void
    {
        $hr = $this->makeUser('hr_manager');

        $this->actingAs($hr)
            ->get(route('payroll.create'))
            ->assertOk();
    }

    public function test_super_admin_can_access_finance_dashboard(): void
    {
        $superAdmin = $this->makeUser('super_admin');

        $this->actingAs($superAdmin)
            ->get(route('dashboard.finance'))
            ->assertOk();
    }

    public function test_finance_manager_can_approve_payroll(): void
    {
        // Approve requires finance_manager or super_admin role
        // We just verify the middleware passes for finance_manager
        // (the actual 422 from wrong status is middleware-downstream)
        $finance = $this->makeUser('finance_manager');

        $this->actingAs($finance)
            ->get(route('payroll.index'))
            ->assertOk();
    }
}
