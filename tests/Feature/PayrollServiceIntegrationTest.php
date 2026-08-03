<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Services\PayrollService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature (integration) tests untuk PayrollService::calculate, detectAnomalies, acknowledgeAnomaly.
 */
class PayrollServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private PayrollService $service;
    private Company $company;
    private Payroll $payroll;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PayrollService();
        $this->company = Company::create(['name' => 'Acme Corp']);
        $this->payroll = Payroll::create([
            'company_id'     => $this->company->id,
            'period'         => '2026-07',
            'period_label'   => 'Juli 2026',
            'status'         => 'draft',
            'employee_count' => 0,
            'gross_total'    => 0,
            'deduction_total'=> 0,
            'net_total'      => 0,
        ]);
    }

    private function makeActiveEmployee(array $overrides = []): Employee
    {
        static $counter = 1;
        return Employee::create(array_merge([
            'company_id'   => $this->company->id,
            'nip'          => 'EMP-' . str_pad((string) ($counter++), 4, '0', STR_PAD_LEFT),
            'name'         => 'Employee ' . $counter,
            'department'   => 'Engineering',
            'position'     => 'Developer',
            'work_status'  => 'active',
            'join_date'    => '2022-01-01',
            'basic_salary' => 5_000_000,
        ], $overrides));
    }

    // -----------------------------------------------------------------------
    // calculate() — core integration
    // -----------------------------------------------------------------------

    public function test_calculate_creates_payroll_items_for_all_active_employees(): void
    {
        $this->makeActiveEmployee();
        $this->makeActiveEmployee();

        $this->service->calculate($this->payroll);

        $this->assertSame(2, PayrollItem::where('payroll_id', $this->payroll->id)->count());
    }

    public function test_calculate_ignores_inactive_employees(): void
    {
        $this->makeActiveEmployee(); // active
        $this->makeActiveEmployee(['work_status' => 'inactive']); // inactive → skip

        $this->service->calculate($this->payroll);

        $this->assertSame(1, PayrollItem::where('payroll_id', $this->payroll->id)->count());
    }

    public function test_calculate_updates_payroll_totals(): void
    {
        $this->makeActiveEmployee(['basic_salary' => 5_000_000]);
        $this->makeActiveEmployee(['basic_salary' => 6_000_000]);

        $this->service->calculate($this->payroll);

        $this->payroll->refresh();
        $this->assertSame(2, $this->payroll->employee_count);
        $this->assertGreaterThan(0, (float) $this->payroll->gross_total);
        $this->assertGreaterThan(0, (float) $this->payroll->net_total);
    }

    public function test_calculate_transitions_status_from_draft_to_needs_review(): void
    {
        $this->makeActiveEmployee();
        $this->assertSame('draft', $this->payroll->status);

        $this->service->calculate($this->payroll);

        $this->assertSame('needs_review', $this->payroll->fresh()->status);
    }

    public function test_calculate_does_not_transition_if_already_needs_review(): void
    {
        $this->makeActiveEmployee();
        $this->payroll->update(['status' => 'needs_review']);

        $this->service->calculate($this->payroll);

        // Status tetap needs_review, tidak maju ke pending_approval
        $this->assertSame('needs_review', $this->payroll->fresh()->status);
    }

    public function test_calculate_updates_existing_payroll_item_on_recalculate(): void
    {
        $employee = $this->makeActiveEmployee(['basic_salary' => 4_000_000]);

        $this->service->calculate($this->payroll);

        // Ubah gaji dan hitung ulang
        $employee->update(['basic_salary' => 6_000_000]);
        $this->payroll->update(['status' => 'needs_review']);
        $this->service->calculate($this->payroll->fresh());

        // Masih hanya 1 item (updateOrCreate, bukan duplikat)
        $this->assertSame(1, PayrollItem::where('payroll_id', $this->payroll->id)->count());

        $item = PayrollItem::where('payroll_id', $this->payroll->id)->first();
        $this->assertEqualsWithDelta(6_000_000.0, (float) $item->basic_salary_snapshot, 0.01);
    }

    // -----------------------------------------------------------------------
    // detectAnomalies()
    // -----------------------------------------------------------------------

    public function test_detect_anomaly_flags_missing_bank_account(): void
    {
        $employee = $this->makeActiveEmployee([
            'bank_name'           => null,
            'bank_account_number' => null,
        ]);

        $this->service->calculate($this->payroll);

        $item = PayrollItem::where('employee_id', $employee->id)->first();
        $this->assertTrue($item->has_anomaly);
        $anomalyTypes = json_decode($item->anomaly_type, true);
        $this->assertContains('no_bank_account', $anomalyTypes);
    }

    public function test_detect_anomaly_flags_unverified_bank(): void
    {
        $employee = $this->makeActiveEmployee([
            'bank_name'           => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_status' => 'unverified',
        ]);

        $this->service->calculate($this->payroll);

        $item = PayrollItem::where('employee_id', $employee->id)->first();
        $this->assertTrue($item->has_anomaly);
        $anomalyTypes = json_decode($item->anomaly_type, true);
        $this->assertContains('unverified_bank', $anomalyTypes);
    }

    public function test_detect_anomaly_flags_missing_attendance(): void
    {
        $employee = $this->makeActiveEmployee([
            'bank_name'           => 'BCA',
            'bank_account_number' => '9999999999',
            'bank_account_status' => 'verified',
        ]);

        // Tidak ada AttendanceRecord untuk payroll ini
        $this->service->calculate($this->payroll);

        $item = PayrollItem::where('employee_id', $employee->id)->first();
        $this->assertTrue($item->has_anomaly);
        $anomalyTypes = json_decode($item->anomaly_type, true);
        $this->assertContains('missing_attendance', $anomalyTypes);
    }

    public function test_no_anomaly_when_everything_is_clean(): void
    {
        $employee = $this->makeActiveEmployee([
            'bank_name'           => 'BCA',
            'bank_account_number' => '9876543210',
            'bank_account_status' => 'verified',
        ]);

        // Tambahkan attendance record
        AttendanceRecord::create([
            'payroll_id'     => $this->payroll->id,
            'employee_id'    => $employee->id,
            'company_id'     => $this->company->id,
            'work_days'      => 22,
            'days_present'   => 22,
            'overtime_hours' => 0,
            'leave_days'     => 0,
        ]);

        $this->service->calculate($this->payroll);

        $item = PayrollItem::where('employee_id', $employee->id)->first();
        $this->assertFalse($item->has_anomaly);
    }

    public function test_anomaly_count_is_updated_on_payroll(): void
    {
        $this->makeActiveEmployee(); // will trigger missing_attendance anomaly

        $this->service->calculate($this->payroll);

        $this->assertGreaterThan(0, $this->payroll->fresh()->anomaly_count);
    }

    // -----------------------------------------------------------------------
    // acknowledgeAnomaly()
    // -----------------------------------------------------------------------

    public function test_acknowledge_anomaly_decrements_payroll_anomaly_count(): void
    {
        $this->makeActiveEmployee(); // triggers anomaly

        $this->service->calculate($this->payroll);

        $item      = PayrollItem::where('payroll_id', $this->payroll->id)->where('has_anomaly', true)->first();
        $countBefore = $this->payroll->fresh()->anomaly_count;

        $this->service->acknowledgeAnomaly($item);

        $countAfter = $this->payroll->fresh()->anomaly_count;
        $this->assertSame($countBefore - 1, $countAfter);
        $this->assertTrue($item->fresh()->anomaly_acknowledged);
    }

    public function test_acknowledge_anomaly_is_idempotent(): void
    {
        $this->makeActiveEmployee();

        $this->service->calculate($this->payroll);

        $item = PayrollItem::where('payroll_id', $this->payroll->id)->where('has_anomaly', true)->first();
        $this->service->acknowledgeAnomaly($item);
        $countAfterFirst  = $this->payroll->fresh()->anomaly_count;

        // Acknowledge lagi — tidak boleh ubah count
        $this->service->acknowledgeAnomaly($item->fresh());
        $countAfterSecond = $this->payroll->fresh()->anomaly_count;

        $this->assertSame($countAfterFirst, $countAfterSecond);
    }

    public function test_acknowledge_anomaly_no_op_when_has_anomaly_false(): void
    {
        $employee = $this->makeActiveEmployee([
            'bank_name'           => 'BRI',
            'bank_account_number' => '1111111111',
            'bank_account_status' => 'verified',
        ]);

        AttendanceRecord::create([
            'payroll_id'     => $this->payroll->id,
            'employee_id'    => $employee->id,
            'company_id'     => $this->company->id,
            'work_days'      => 22,
            'days_present'   => 22,
            'overtime_hours' => 0,
            'leave_days'     => 0,
        ]);

        $this->service->calculate($this->payroll);

        $item = PayrollItem::where('employee_id', $employee->id)->first();
        $this->assertFalse($item->has_anomaly);

        // Panggil acknowledge pada item yang tidak ada anomali — no-op
        $this->service->acknowledgeAnomaly($item);

        $this->assertFalse($item->fresh()->anomaly_acknowledged);
    }

    // -----------------------------------------------------------------------
    // canSubmit()
    // -----------------------------------------------------------------------

    public function test_can_submit_returns_false_when_unacknowledged_anomalies_exist(): void
    {
        $this->makeActiveEmployee(); // triggers anomaly

        $this->service->calculate($this->payroll);

        $this->assertFalse($this->service->canSubmit($this->payroll->fresh()));
    }

    public function test_can_submit_returns_true_when_all_anomalies_acknowledged(): void
    {
        $this->makeActiveEmployee();

        $this->service->calculate($this->payroll);

        $item = PayrollItem::where('payroll_id', $this->payroll->id)->where('has_anomaly', true)->first();
        $this->service->acknowledgeAnomaly($item);

        $this->assertTrue($this->service->canSubmit($this->payroll->fresh()));
    }

    public function test_can_submit_returns_true_when_no_anomalies(): void
    {
        $employee = $this->makeActiveEmployee([
            'bank_name'           => 'Mandiri',
            'bank_account_number' => '2222222222',
            'bank_account_status' => 'verified',
        ]);

        AttendanceRecord::create([
            'payroll_id'     => $this->payroll->id,
            'employee_id'    => $employee->id,
            'company_id'     => $this->company->id,
            'work_days'      => 22,
            'days_present'   => 22,
            'overtime_hours' => 0,
            'leave_days'     => 0,
        ]);

        $this->service->calculate($this->payroll);

        $this->assertTrue($this->service->canSubmit($this->payroll->fresh()));
    }
}
