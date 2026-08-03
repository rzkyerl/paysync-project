<?php

namespace Tests\Unit;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Services\PayrollService;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests lanjutan untuk PayrollService::calculateItemForEmployee.
 * Melengkapi PayrollServiceTest yang sudah ada.
 */
class PayrollServiceAdvancedTest extends TestCase
{
    private PayrollService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PayrollService();
    }

    // -----------------------------------------------------------------------
    // Absence deduction
    // -----------------------------------------------------------------------

    public function test_absence_deduction_applied_when_days_present_less_than_work_days(): void
    {
        $employee   = new Employee(['basic_salary' => 4_400_000]);
        $attendance = new AttendanceRecord([
            'work_days'      => 22,
            'days_present'   => 20,
            'leave_days'     => 0,
            'overtime_hours' => 0,
        ]);

        $item = $this->service->calculateItemForEmployee($employee, $attendance);

        // 2 hari tidak hadir: 4_400_000 / 22 * 2
        $expectedAbsence = 4_400_000 / 22 * 2;
        $this->assertEqualsWithDelta($expectedAbsence, $item['absence_deduction'], 0.01);
    }

    public function test_leave_days_are_not_deducted_as_absent(): void
    {
        $employee   = new Employee(['basic_salary' => 4_400_000]);
        $attendance = new AttendanceRecord([
            'work_days'      => 22,
            'days_present'   => 20,
            'leave_days'     => 2, // 2 hari cuti berbayar → tidak ada potongan absensi
            'overtime_hours' => 0,
        ]);

        $item = $this->service->calculateItemForEmployee($employee, $attendance);

        // absent_days = 22 - 20 - 2 = 0 → tidak ada potongan absensi
        $this->assertEqualsWithDelta(0.0, $item['absence_deduction'], 0.01);
    }

    public function test_gross_pay_reduced_by_absence_deduction(): void
    {
        $employee   = new Employee(['basic_salary' => 5_000_000]);
        $attendance = new AttendanceRecord([
            'work_days'      => 22,
            'days_present'   => 18,
            'leave_days'     => 0,
            'overtime_hours' => 0,
        ]);

        $item = $this->service->calculateItemForEmployee($employee, $attendance);

        $absentDays      = 22 - 18;
        $absenceDeduction = 5_000_000 / 22 * $absentDays;
        $expectedGross    = (5_000_000 - $absenceDeduction);

        $this->assertEqualsWithDelta($expectedGross, $item['gross_pay'], 0.01);
    }

    // -----------------------------------------------------------------------
    // PPh21 threshold boundary
    // -----------------------------------------------------------------------

    public function test_pph21_is_zero_when_gross_equals_threshold(): void
    {
        // gross_pay tepat di threshold → 4_500_000 → PPh21 = 0
        $employee = new Employee(['basic_salary' => 4_500_000]);

        $item = $this->service->calculateItemForEmployee($employee, null);

        $this->assertEqualsWithDelta(0.0, $item['pph21_deduction'], 0.01);
    }

    public function test_pph21_is_zero_when_gross_below_threshold(): void
    {
        $employee = new Employee(['basic_salary' => 3_000_000]);

        $item = $this->service->calculateItemForEmployee($employee, null);

        $this->assertEqualsWithDelta(0.0, $item['pph21_deduction'], 0.01);
    }

    public function test_pph21_applied_when_gross_exceeds_threshold(): void
    {
        $employee = new Employee(['basic_salary' => 6_000_000]);

        $item = $this->service->calculateItemForEmployee($employee, null);

        $expectedPph21 = (6_000_000 - PayrollService::PPH21_THRESHOLD) * PayrollService::PPH21_RATE;
        $this->assertEqualsWithDelta($expectedPph21, $item['pph21_deduction'], 0.01);
    }

    // -----------------------------------------------------------------------
    // Zero salary edge case
    // -----------------------------------------------------------------------

    public function test_zero_basic_salary_produces_zero_net_pay(): void
    {
        $employee = new Employee(['basic_salary' => 0]);

        $item = $this->service->calculateItemForEmployee($employee, null);

        $this->assertEqualsWithDelta(0.0, $item['gross_pay'], 0.01);
        $this->assertEqualsWithDelta(0.0, $item['net_pay'], 0.01);
        $this->assertEqualsWithDelta(0.0, $item['total_deduction'], 0.01);
    }

    // -----------------------------------------------------------------------
    // Snapshot values
    // -----------------------------------------------------------------------

    public function test_snapshots_are_stored_correctly(): void
    {
        $employee   = new Employee(['basic_salary' => 5_000_000]);
        $attendance = new AttendanceRecord([
            'work_days'      => 20,
            'days_present'   => 18,
            'leave_days'     => 1,
            'overtime_hours' => 5,
        ]);

        $item = $this->service->calculateItemForEmployee($employee, $attendance);

        $this->assertSame(18, $item['days_present_snapshot']);
        $this->assertSame(20, $item['work_days_snapshot']);
        $this->assertEqualsWithDelta(5_000_000.0, $item['basic_salary_snapshot'], 0.01);
    }

    // -----------------------------------------------------------------------
    // Full formula with absence + overtime
    // -----------------------------------------------------------------------

    public function test_full_formula_with_absence_and_overtime(): void
    {
        $salary     = 6_000_000;
        $workDays   = 22;
        $present    = 20;
        $leave      = 1;
        $overtime   = 3;

        $employee   = new Employee(['basic_salary' => $salary]);
        $attendance = new AttendanceRecord([
            'work_days'      => $workDays,
            'days_present'   => $present,
            'leave_days'     => $leave,
            'overtime_hours' => $overtime,
        ]);

        $item = $this->service->calculateItemForEmployee($employee, $attendance);

        $absentDays       = $workDays - $present - $leave; // 1
        $absenceDeduction = ($salary / $workDays) * $absentDays;
        $effectiveSalary  = $salary - $absenceDeduction;
        $overtimePay      = $overtime * ($salary / PayrollService::OVERTIME_DIVISOR * PayrollService::OVERTIME_MULTIPLIER);
        $gross            = $effectiveSalary + $overtimePay;
        $bpjsTk           = $gross * PayrollService::BPJS_TK_RATE;
        $bpjsKes          = $gross * PayrollService::BPJS_KES_RATE;
        $pph21            = $gross > PayrollService::PPH21_THRESHOLD
            ? ($gross - PayrollService::PPH21_THRESHOLD) * PayrollService::PPH21_RATE
            : 0;
        $totalDeduction   = $bpjsTk + $bpjsKes + $pph21;
        $net              = $gross - $totalDeduction;

        $this->assertEqualsWithDelta($gross, $item['gross_pay'], 0.01);
        $this->assertEqualsWithDelta($totalDeduction, $item['total_deduction'], 0.01);
        $this->assertEqualsWithDelta($net, $item['net_pay'], 0.01);
        $this->assertEqualsWithDelta($absenceDeduction, $item['absence_deduction'], 0.01);
        $this->assertEqualsWithDelta($overtimePay, $item['overtime_pay'], 0.01);
    }
}
