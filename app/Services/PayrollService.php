<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    public const OVERTIME_DIVISOR = 173;

    public const OVERTIME_MULTIPLIER = 1.5;

    public const BPJS_TK_RATE = 0.02;

    public const BPJS_KES_RATE = 0.01;

    public const PPH21_THRESHOLD = 4_500_000;

    public const PPH21_RATE = 0.05;

    public function calculateItemForEmployee(Employee $employee, ?AttendanceRecord $attendance): array
    {
        $basicSalary = (float) $employee->basic_salary;
        $overtimeHours = (float) ($attendance?->overtime_hours ?? 0);
        $overtimePay = $overtimeHours * ($basicSalary / self::OVERTIME_DIVISOR * self::OVERTIME_MULTIPLIER);
        $grossPay = $basicSalary + $overtimePay;
        $bpjsTk = $grossPay * self::BPJS_TK_RATE;
        $bpjsKes = $grossPay * self::BPJS_KES_RATE;
        $pph21 = $grossPay > self::PPH21_THRESHOLD
            ? ($grossPay - self::PPH21_THRESHOLD) * self::PPH21_RATE
            : 0;
        $totalDeduction = $bpjsTk + $bpjsKes + $pph21;

        return [
            'gross_pay' => round($grossPay, 2),
            'basic_salary_snapshot' => round($basicSalary, 2),
            'overtime_pay' => round($overtimePay, 2),
            'bpjs_tk_deduction' => round($bpjsTk, 2),
            'bpjs_kesehatan_deduction' => round($bpjsKes, 2),
            'pph21_deduction' => round($pph21, 2),
            'total_deduction' => round($totalDeduction, 2),
            'net_pay' => round($grossPay - $totalDeduction, 2),
        ];
    }

    public function calculate(Payroll $payroll): void
    {
        DB::transaction(function () use ($payroll): void {
            $employees = Employee::query()
                ->where('company_id', $payroll->company_id)
                ->where('work_status', 'active')
                ->get();
            $attendance = $payroll->attendanceRecords()->get()->keyBy('employee_id');
            $gross = $deductions = $net = 0;

            foreach ($employees as $employee) {
                $components = $this->calculateItemForEmployee($employee, $attendance->get($employee->id));
                $payroll->payrollItems()->updateOrCreate(
                    ['employee_id' => $employee->id],
                    array_merge($components, ['company_id' => $payroll->company_id, 'status' => 'pending']),
                );
                $gross += $components['gross_pay'];
                $deductions += $components['total_deduction'];
                $net += $components['net_pay'];
            }

            $payroll->update([
                'employee_count' => $employees->count(),
                'gross_total' => round($gross, 2),
                'deduction_total' => round($deductions, 2),
                'net_total' => round($net, 2),
            ]);
            $this->detectAnomalies($payroll->fresh());

            if ($payroll->status === 'draft') {
                $payroll->refresh()->transitionTo('needs_review');
            }
        });
    }

    public function detectAnomalies(Payroll $payroll): Collection
    {
        $items = $payroll->payrollItems()->with('employee')->get();

        // Load attendance employee IDs once — avoid N+1 query inside loop
        $attendanceEmployeeIds = $payroll->attendanceRecords()
            ->pluck('employee_id')
            ->flip(); // key by employee_id for O(1) lookup

        foreach ($items as $item) {
            $employee = $item->employee;
            $types = [];
            if (! $employee || (! $employee->bank_account_number && ! $employee->bank_name)) {
                $types[] = 'no_bank_account';
            }
            if ($employee && in_array($employee->bank_account_status, ['unverified', 'rejected'], true)) {
                $types[] = 'unverified_bank';
            }
            if ((float) $item->net_pay === 0.0) {
                $types[] = 'zero_net_pay';
            }
            if (! $attendanceEmployeeIds->has($item->employee_id)) {
                $types[] = 'missing_attendance';
            }
            $item->update([
                'has_anomaly' => $types !== [],
                'anomaly_type' => $types === [] ? null : json_encode($types),
                'anomaly_acknowledged' => $types === [] ? false : $item->anomaly_acknowledged,
            ]);
        }

        $count = $items->filter(fn (PayrollItem $item) => $item->has_anomaly && ! $item->anomaly_acknowledged)->count();
        $payroll->update(['anomaly_count' => $count]);

        return $payroll->payrollItems()->where('has_anomaly', true)->get();
    }

    public function acknowledgeAnomaly(PayrollItem $item): void
    {
        if (! $item->has_anomaly || $item->anomaly_acknowledged) {
            return;
        }
        $item->update(['anomaly_acknowledged' => true]);
        $item->payroll()->update([
            'anomaly_count' => max(0, (int) $item->payroll->anomaly_count - 1),
        ]);
    }

    public function canSubmit(Payroll $payroll): bool
    {
        return ! $payroll->payrollItems()->where('has_anomaly', true)->where('anomaly_acknowledged', false)->exists();
    }
}
