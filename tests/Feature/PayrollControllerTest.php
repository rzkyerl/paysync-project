<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Feature tests untuk PayrollController.
 */
class PayrollControllerTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $hrManager;
    private User $financeManager;
    private User $superAdmin;

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

        $this->superAdmin = User::factory()->create([
            'role'       => 'super_admin',
            'status'     => 'active',
            'company_id' => $this->company->id,
        ]);

        Mail::fake();
    }

    private function makeEmployee(array $overrides = []): Employee
    {
        static $counter = 1;
        return Employee::create(array_merge([
            'company_id'          => $this->company->id,
            'nip'                 => 'EMP-' . str_pad((string) ($counter++), 4, '0', STR_PAD_LEFT),
            'name'                => 'Employee ' . $counter,
            'department'          => 'IT',
            'position'            => 'Staff',
            'work_status'         => 'active',
            'join_date'           => '2022-01-01',
            'basic_salary'        => 5_000_000,
            'bank_name'           => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_status' => 'verified',
        ], $overrides));
    }

    private function makePayroll(array $overrides = []): Payroll
    {
        return Payroll::create(array_merge([
            'company_id'     => $this->company->id,
            'period'         => '2026-07',
            'period_label'   => 'Juli 2026',
            'status'         => 'draft',
            'employee_count' => 0,
            'gross_total'    => 0,
            'deduction_total'=> 0,
            'net_total'      => 0,
        ], $overrides));
    }

    // -----------------------------------------------------------------------
    // storeNew
    // -----------------------------------------------------------------------

    public function test_hr_manager_can_create_new_payroll(): void
    {
        $this->actingAs($this->hrManager)
            ->post(route('payroll.store'), [
                'period'       => '2026-08',
                'period_label' => 'Agustus 2026',
            ])->assertRedirect();

        $this->assertDatabaseHas('payrolls', [
            'company_id' => $this->company->id,
            'period'     => '2026-08',
            'status'     => 'draft',
        ]);
    }

    public function test_create_payroll_fails_with_duplicate_period(): void
    {
        $this->makePayroll(['period' => '2026-08']);

        $this->actingAs($this->hrManager)
            ->post(route('payroll.store'), [
                'period'       => '2026-08',
                'period_label' => 'Agustus 2026',
            ])->assertSessionHasErrors('period');
    }

    public function test_employee_cannot_create_payroll(): void
    {
        $employeeUser = User::factory()->create([
            'role'       => 'employee',
            'status'     => 'active',
            'company_id' => $this->company->id,
        ]);

        $this->actingAs($employeeUser)
            ->post(route('payroll.store'), [
                'period'       => '2026-09',
                'period_label' => 'September 2026',
            ])->assertForbidden();
    }

    public function test_create_payroll_requires_valid_period_format(): void
    {
        $this->actingAs($this->hrManager)
            ->post(route('payroll.store'), [
                'period'       => '07-2026', // format salah
                'period_label' => 'Juli 2026',
            ])->assertSessionHasErrors('period');
    }

    // -----------------------------------------------------------------------
    // submit
    // -----------------------------------------------------------------------

    public function test_hr_manager_can_submit_payroll_without_anomalies(): void
    {
        $payroll = $this->makePayroll(['status' => 'needs_review']);

        $this->actingAs($this->hrManager)
            ->put(route('payroll.submit', $payroll))
            ->assertRedirect();

        $this->assertSame('pending_approval', $payroll->fresh()->status);
    }

    public function test_submit_fails_when_unacknowledged_anomalies_exist(): void
    {
        $employee = $this->makeEmployee(['bank_account_status' => 'unverified']);
        $payroll  = $this->makePayroll(['status' => 'needs_review']);

        PayrollItem::create([
            'payroll_id'       => $payroll->id,
            'employee_id'      => $employee->id,
            'company_id'       => $this->company->id,
            'gross_pay'        => 5_000_000,
            'basic_salary_snapshot' => 5_000_000,
            'net_pay'          => 4_650_000,
            'total_deduction'  => 350_000,
            'status'           => 'pending',
            'has_anomaly'      => true,
            'anomaly_acknowledged' => false,
        ]);

        $this->actingAs($this->hrManager)
            ->put(route('payroll.submit', $payroll))
            ->assertSessionHas('error');
    }

    public function test_submit_requires_needs_review_status(): void
    {
        $payroll = $this->makePayroll(['status' => 'draft']); // wrong status

        $this->actingAs($this->hrManager)
            ->put(route('payroll.submit', $payroll))
            ->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // approve
    // -----------------------------------------------------------------------

    public function test_finance_manager_can_approve_pending_payroll(): void
    {
        $payroll = $this->makePayroll(['status' => 'pending_approval']);

        $this->actingAs($this->financeManager)
            ->post(route('payroll.approve', $payroll))
            ->assertRedirect();

        $this->assertSame('approved', $payroll->fresh()->status);
    }

    public function test_hr_manager_cannot_approve_payroll(): void
    {
        $payroll = $this->makePayroll(['status' => 'pending_approval']);

        $this->actingAs($this->hrManager)
            ->post(route('payroll.approve', $payroll))
            ->assertForbidden();
    }

    public function test_approve_requires_pending_approval_status(): void
    {
        $payroll = $this->makePayroll(['status' => 'draft']); // wrong status

        $this->actingAs($this->financeManager)
            ->post(route('payroll.approve', $payroll))
            ->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // reject
    // -----------------------------------------------------------------------

    public function test_finance_manager_can_reject_payroll_with_note(): void
    {
        $payroll = $this->makePayroll(['status' => 'pending_approval']);

        $this->actingAs($this->financeManager)
            ->post(route('payroll.reject', $payroll), [
                'rejection_note' => 'Ada anomali yang belum diselesaikan.',
            ])->assertRedirect();

        $this->assertSame('needs_review', $payroll->fresh()->status);
        $this->assertSame('Ada anomali yang belum diselesaikan.', $payroll->fresh()->rejection_note);
    }

    public function test_reject_requires_rejection_note(): void
    {
        $payroll = $this->makePayroll(['status' => 'pending_approval']);

        $this->actingAs($this->financeManager)
            ->post(route('payroll.reject', $payroll), [])
            ->assertSessionHasErrors('rejection_note');
    }

    public function test_reject_requires_pending_approval_status(): void
    {
        $payroll = $this->makePayroll(['status' => 'approved']); // wrong status

        $this->actingAs($this->financeManager)
            ->post(route('payroll.reject', $payroll), [
                'rejection_note' => 'Perlu diperbaiki.',
            ])->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // disburse
    // -----------------------------------------------------------------------

    public function test_finance_manager_can_disburse_approved_payroll(): void
    {
        $employee = $this->makeEmployee();
        $payroll  = $this->makePayroll([
            'status'         => 'approved',
            'employee_count' => 1,
            'net_total'      => 4_650_000,
        ]);

        PayrollItem::create([
            'payroll_id'            => $payroll->id,
            'employee_id'           => $employee->id,
            'company_id'            => $this->company->id,
            'gross_pay'             => 5_000_000,
            'basic_salary_snapshot' => 5_000_000,
            'net_pay'               => 4_650_000,
            'total_deduction'       => 350_000,
            'status'                => 'pending',
        ]);

        $this->actingAs($this->financeManager)
            ->post(route('payroll.disburse', $payroll))
            ->assertRedirect();

        $this->assertSame('disbursed', $payroll->fresh()->status);
        $this->assertDatabaseHas('payroll_items', [
            'payroll_id' => $payroll->id,
            'status'     => 'transferred',
        ]);
    }

    public function test_disburse_requires_approved_status(): void
    {
        $payroll = $this->makePayroll(['status' => 'pending_approval']); // wrong status

        $this->actingAs($this->financeManager)
            ->post(route('payroll.disburse', $payroll))
            ->assertStatus(422);
    }

    public function test_hr_manager_cannot_disburse(): void
    {
        $payroll = $this->makePayroll(['status' => 'approved']);

        $this->actingAs($this->hrManager)
            ->post(route('payroll.disburse', $payroll))
            ->assertForbidden();
    }

    // -----------------------------------------------------------------------
    // Cross-company access
    // -----------------------------------------------------------------------

    public function test_user_cannot_access_payroll_from_different_company(): void
    {
        $otherCompany = Company::create(['name' => 'Other Corp']);
        $otherPayroll = Payroll::create([
            'company_id'     => $otherCompany->id,
            'period'         => '2026-07',
            'period_label'   => 'Juli 2026',
            'status'         => 'pending_approval',
            'employee_count' => 0,
            'gross_total'    => 0,
            'deduction_total'=> 0,
            'net_total'      => 0,
        ]);

        $this->actingAs($this->financeManager)
            ->post(route('payroll.approve', $otherPayroll))
            ->assertNotFound();
    }

    // -----------------------------------------------------------------------
    // payslip
    // -----------------------------------------------------------------------

    public function test_employee_can_access_own_payslip(): void
    {
        $employee = $this->makeEmployee();

        $employeeUser = User::factory()->create([
            'role'        => 'employee',
            'status'      => 'active',
            'company_id'  => $this->company->id,
            'employee_id' => $employee->id,
        ]);

        $payroll = $this->makePayroll(['status' => 'disbursed']);

        PayrollItem::create([
            'payroll_id'            => $payroll->id,
            'employee_id'           => $employee->id,
            'company_id'            => $this->company->id,
            'gross_pay'             => 5_000_000,
            'basic_salary_snapshot' => 5_000_000,
            'net_pay'               => 4_650_000,
            'total_deduction'       => 350_000,
            'status'                => 'transferred',
        ]);

        $this->actingAs($employeeUser)
            ->get(route('payroll.payslip', [$payroll, $employee]))
            ->assertOk();
    }

    public function test_employee_cannot_access_another_employees_payslip(): void
    {
        $employee1 = $this->makeEmployee();
        $employee2 = $this->makeEmployee(['nip' => 'EMP-9999']);

        $employeeUser = User::factory()->create([
            'role'        => 'employee',
            'status'      => 'active',
            'company_id'  => $this->company->id,
            'employee_id' => $employee1->id,
        ]);

        $payroll = $this->makePayroll(['status' => 'disbursed']);

        PayrollItem::create([
            'payroll_id'            => $payroll->id,
            'employee_id'           => $employee2->id,
            'company_id'            => $this->company->id,
            'gross_pay'             => 5_000_000,
            'basic_salary_snapshot' => 5_000_000,
            'net_pay'               => 4_650_000,
            'total_deduction'       => 350_000,
            'status'                => 'transferred',
        ]);

        $this->actingAs($employeeUser)
            ->get(route('payroll.payslip', [$payroll, $employee2]))
            ->assertForbidden();
    }

    // -----------------------------------------------------------------------
    // myPayslips
    // -----------------------------------------------------------------------

    public function test_employee_can_view_own_payslips_list(): void
    {
        $employee = $this->makeEmployee();

        $employeeUser = User::factory()->create([
            'role'        => 'employee',
            'status'      => 'active',
            'company_id'  => $this->company->id,
            'employee_id' => $employee->id,
        ]);

        $this->actingAs($employeeUser)
            ->get(route('payroll.my-payslips'))
            ->assertOk();
    }

    public function test_non_employee_cannot_access_my_payslips(): void
    {
        // Middleware role pada GET request mengarahkan ke dashboard, bukan 403
        $this->actingAs($this->hrManager)
            ->get(route('payroll.my-payslips'))
            ->assertRedirect('/app/dashboard-hr');
    }

    // -----------------------------------------------------------------------
    // dispute
    // -----------------------------------------------------------------------

    public function test_finance_manager_can_dispute_disbursed_payroll(): void
    {
        $payroll = $this->makePayroll(['status' => 'disbursed']);

        $this->actingAs($this->financeManager)
            ->post(route('payroll.dispute', $payroll), [
                'dispute_reason' => 'Dana tidak sesuai dengan slip gaji.',
            ])->assertRedirect();

        $payroll->refresh();
        $this->assertSame('disputed', $payroll->status);
        $this->assertSame('Dana tidak sesuai dengan slip gaji.', $payroll->dispute_reason);
        $this->assertSame($this->financeManager->id, $payroll->disputed_by);
    }

    public function test_dispute_requires_reason(): void
    {
        $payroll = $this->makePayroll(['status' => 'disbursed']);

        $this->actingAs($this->financeManager)
            ->post(route('payroll.dispute', $payroll), [])
            ->assertSessionHasErrors('dispute_reason');
    }

    public function test_dispute_requires_disbursed_status(): void
    {
        $payroll = $this->makePayroll(['status' => 'approved']);

        $this->actingAs($this->financeManager)
            ->post(route('payroll.dispute', $payroll), [
                'dispute_reason' => 'Revisi.',
            ])->assertStatus(422);
    }

    public function test_hr_manager_cannot_dispute(): void
    {
        $payroll = $this->makePayroll(['status' => 'disbursed']);

        $this->actingAs($this->hrManager)
            ->post(route('payroll.dispute', $payroll), [
                'dispute_reason' => 'Revisi.',
            ])->assertForbidden();
    }

    // -----------------------------------------------------------------------
    // reprocess
    // -----------------------------------------------------------------------

    public function test_finance_manager_can_reprocess_disputed_payroll(): void
    {
        $payroll = $this->makePayroll(['status' => 'disputed']);

        $this->actingAs($this->financeManager)
            ->post(route('payroll.reprocess', $payroll))
            ->assertRedirect();

        $payroll->refresh();
        $this->assertSame('approved', $payroll->status);
        $this->assertSame($this->financeManager->id, $payroll->reprocessed_by);
    }

    public function test_reprocess_requires_disputed_status(): void
    {
        $payroll = $this->makePayroll(['status' => 'approved']);

        $this->actingAs($this->financeManager)
            ->post(route('payroll.reprocess', $payroll))
            ->assertStatus(422);
    }

    public function test_hr_manager_can_reprocess(): void
    {
        $payroll = $this->makePayroll(['status' => 'disputed']);

        $this->actingAs($this->hrManager)
            ->post(route('payroll.reprocess', $payroll))
            ->assertRedirect();

        $this->assertSame('approved', $payroll->fresh()->status);
    }

    public function test_dispute_reprocess_disburse_round_trip(): void
    {
        $employee = $this->makeEmployee();
        $payroll  = $this->makePayroll([
            'status'         => 'disbursed',
            'employee_count' => 1,
            'net_total'      => 4_650_000,
            'gross_total'    => 5_000_000,
            'deduction_total'=> 350_000,
        ]);

        PayrollItem::create([
            'payroll_id'            => $payroll->id,
            'employee_id'           => $employee->id,
            'company_id'            => $this->company->id,
            'gross_pay'             => 5_000_000,
            'basic_salary_snapshot' => 5_000_000,
            'net_pay'               => 4_500_000,
            'total_deduction'       => 500_000,
            'status'                => 'transferred',
        ]);

        // 1. Finance disputes
        $this->actingAs($this->financeManager)
            ->post(route('payroll.dispute', $payroll), [
                'dispute_reason' => 'Nominal tidak sesuai.',
            ])->assertRedirect();

        $payroll->refresh();
        $this->assertSame('disputed', $payroll->status);

        // 2. HR reprocesses back to approved
        $this->actingAs($this->hrManager)
            ->post(route('payroll.reprocess', $payroll))
            ->assertRedirect();

        $payroll->refresh();
        $this->assertSame('approved', $payroll->status);
        $this->assertSame($this->hrManager->id, $payroll->reprocessed_by);

        // 3. Finance disburses again
        $this->actingAs($this->financeManager)
            ->post(route('payroll.disburse', $payroll))
            ->assertRedirect();

        $payroll->refresh();
        $this->assertSame('disbursed', $payroll->status);
        $this->assertDatabaseHas('payroll_items', [
            'payroll_id' => $payroll->id,
            'status'     => 'transferred',
        ]);
    }

    public function test_disputed_cannot_transition_to_needs_review(): void
    {
        $payroll = $this->makePayroll(['status' => 'disputed']);

        // Verify that disputed → needs_review is blocked (dead path removed)
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Invalid payroll transition from disputed to needs_review');

        $payroll->transitionTo('needs_review');
    }
}