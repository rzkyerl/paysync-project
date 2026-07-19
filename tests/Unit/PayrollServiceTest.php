<?php

namespace Tests\Unit;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Services\PayrollService;
use PHPUnit\Framework\TestCase;

class PayrollServiceTest extends TestCase
{
    public function test_calculate_item_applies_overtime_and_deductions_formula(): void
    {
        $employee = new Employee(['basic_salary' => 5_000_000]);
        $attendance = new AttendanceRecord(['overtime_hours' => 10]);

        $item = (new PayrollService)->calculateItemForEmployee($employee, $attendance);

        $expectedOvertime = 10 * (5_000_000 / 173 * 1.5);
        $expectedGross = 5_000_000 + $expectedOvertime;
        $expectedDeduction = ($expectedGross * 0.02) + ($expectedGross * 0.01) + (($expectedGross - 4_500_000) * 0.05);

        $this->assertEqualsWithDelta($expectedGross, $item['gross_pay'], 0.01);
        $this->assertEqualsWithDelta($expectedDeduction, $item['total_deduction'], 0.01);
        $this->assertEqualsWithDelta($expectedGross - $expectedDeduction, $item['net_pay'], 0.01);
    }

    public function test_calculate_item_without_attendance_has_no_overtime(): void
    {
        $item = (new PayrollService)->calculateItemForEmployee(new Employee(['basic_salary' => 4_000_000]), null);

        $this->assertSame(0.0, $item['overtime_pay']);
        $this->assertSame(4_000_000.0, $item['gross_pay']);
        $this->assertSame(0.0, $item['pph21_deduction']);
    }
}
