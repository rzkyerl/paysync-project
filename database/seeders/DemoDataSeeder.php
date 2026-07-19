<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\User;
use App\Services\PayrollService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed the reusable PaySync demo workspace.
     *
     * Roles covered:
     *   super_admin     — Owner/CEO   → Ahmad Fauzi    (DEMO-0001)
     *   hr_manager      — HR Manager  → Rina Maharani  (DEMO-0002)
     *   finance_manager — Finance     → Budi Santoso   (DEMO-0003)
     *   employee        — Staff       → Sari Wijaya    (DEMO-0004)
     *
     * Extra staff (no user account, mixed work_status for realism):
     *   DEMO-0005 … DEMO-0008
     *
     * Payroll periods seeded:
     *   T-2 months  → disbursed       (no anomalies, all items transferred)
     *   T-1 month   → pending_approval (2 anomalies acknowledged)
     *   This month  → needs_review    (2 unacknowledged anomalies)
     */
    public function run(): void
    {
        $service = app(PayrollService::class);

        // ----------------------------------------------------------------
        // 1. Company
        // ----------------------------------------------------------------
        $company = Company::firstOrCreate(
            ['name' => 'PT Pay Sync'],
            [
                'is_demo'         => true,
                'industry'        => 'Technology',
                'size'            => '51-200',
                'payroll_cut_off' => 25,
                'pay_date'        => 30,
            ]
        );
        $company->update(['is_demo' => true]);

        // ----------------------------------------------------------------
        // 2. Employees
        // ----------------------------------------------------------------
        $employeeDefinitions = [
            [
                'nip'                 => 'DEMO-0001',
                'name'                => 'Ahmad Fauzi',
                'department'          => 'Executive',
                'position'            => 'Chief Executive Officer',
                'work_status'         => 'active',
                'join_date'           => '2020-01-02',
                'bank_account_status' => 'verified',
                'bank_account_number' => '8800010001',
                'bank_name'           => 'BCA',
                'basic_salary'        => 35000000,
            ],
            [
                'nip'                 => 'DEMO-0002',
                'name'                => 'Rina Maharani',
                'department'          => 'People & Culture',
                'position'            => 'HR Manager',
                'work_status'         => 'active',
                'join_date'           => '2020-03-15',
                'bank_account_status' => 'verified',
                'bank_account_number' => '8800010002',
                'bank_name'           => 'Mandiri',
                'basic_salary'        => 18000000,
            ],
            [
                'nip'                 => 'DEMO-0003',
                'name'                => 'Budi Santoso',
                'department'          => 'Finance',
                'position'            => 'Finance Manager',
                'work_status'         => 'active',
                'join_date'           => '2020-06-01',
                'bank_account_status' => 'verified',
                'bank_account_number' => '8800010003',
                'bank_name'           => 'BNI',
                'basic_salary'        => 20000000,
            ],
            [
                'nip'                 => 'DEMO-0004',
                'name'                => 'Sari Wijaya',
                'department'          => 'Engineering',
                'position'            => 'Backend Developer',
                'work_status'         => 'active',
                'join_date'           => '2022-08-01',
                'bank_account_status' => 'verified',
                'bank_account_number' => '8800010004',
                'bank_name'           => 'BCA',
                'basic_salary'        => 14000000,
            ],
            [
                'nip'                 => 'DEMO-0005',
                'name'                => 'Andi Pratama',
                'department'          => 'Engineering',
                'position'            => 'Frontend Developer',
                'work_status'         => 'active',
                'join_date'           => '2023-02-20',
                'bank_account_status' => 'verified',
                'bank_account_number' => '8800010005',
                'bank_name'           => 'BRI',
                'basic_salary'        => 13000000,
            ],
            // probation — tidak ikut kalkulasi payroll (work_status bukan active)
            [
                'nip'                 => 'DEMO-0006',
                'name'                => 'Maya Putri',
                'department'          => 'Marketing',
                'position'            => 'Marketing Specialist',
                'work_status'         => 'probation',
                'join_date'           => '2026-04-14',
                'bank_account_status' => 'unverified',
                'bank_account_number' => null,
                'bank_name'           => null,
                'basic_salary'        => 9000000,
            ],
            // contract — tidak ikut kalkulasi payroll
            [
                'nip'                 => 'DEMO-0007',
                'name'                => 'Deni Kurniawan',
                'department'          => 'Operations',
                'position'            => 'Operations Staff',
                'work_status'         => 'contract',
                'join_date'           => '2025-01-10',
                'bank_account_status' => 'verified',
                'bank_account_number' => '8800010007',
                'bank_name'           => 'CIMB Niaga',
                'basic_salary'        => 8500000,
            ],
            // active tapi rekening rejected → anomali di payroll bulan ini
            [
                'nip'                 => 'DEMO-0008',
                'name'                => 'Fitri Handayani',
                'department'          => 'Engineering',
                'position'            => 'QA Engineer',
                'work_status'         => 'active',
                'join_date'           => '2024-07-01',
                'bank_account_status' => 'rejected',
                'bank_account_number' => '8800010008',
                'bank_name'           => 'Mandiri',
                'basic_salary'        => 11000000,
            ],
        ];

        $employees = [];
        foreach ($employeeDefinitions as $definition) {
            $definition['company_id'] = $company->id;
            $employees[$definition['nip']] = Employee::firstOrCreate(
                ['nip' => $definition['nip']],
                $definition
            );
        }

        // Active employees yang ikut kalkulasi payroll
        $activeEmployees = collect($employees)->filter(
            fn (Employee $e) => $e->work_status === 'active'
        );

        // ----------------------------------------------------------------
        // 3. Users
        // ----------------------------------------------------------------
        $userDefinitions = [
            ['email' => 'ceo@paysync.test',      'name' => 'Ahmad Fauzi',   'role' => 'super_admin',     'employee' => 'DEMO-0001'],
            ['email' => 'hr@paysync.test',        'name' => 'Rina Maharani', 'role' => 'hr_manager',      'employee' => 'DEMO-0002'],
            ['email' => 'finance@paysync.test',   'name' => 'Budi Santoso',  'role' => 'finance_manager', 'employee' => 'DEMO-0003'],
            ['email' => 'employee@paysync.test',  'name' => 'Sari Wijaya',   'role' => 'employee',        'employee' => 'DEMO-0004'],
        ];

        $seededUsers = [];
        foreach ($userDefinitions as $definition) {
            $seededUsers[$definition['role']] = User::updateOrCreate(
                ['email' => $definition['email']],
                [
                    'name'              => $definition['name'],
                    'password'          => Hash::make('password'),
                    'role'              => $definition['role'],
                    'employee_id'       => $employees[$definition['employee']]->id,
                    'is_demo'           => true,
                    'company_id'        => $company->id,
                    'email_verified_at' => now(),
                    'status'            => 'active',
                ]
            );
        }

        $hrUser      = $seededUsers['hr_manager'];
        $financeUser = $seededUsers['finance_manager'];

        // ----------------------------------------------------------------
        // 4. Payroll periods + PayrollItems + AttendanceRecords
        // ----------------------------------------------------------------

        // Attendance helper: karyawan hadir normal kecuali yang punya anomali
        // $attendanceMap = [ nip => ['days_present', 'overtime_hours', 'leave_days'] ]
        $normalAttendance    = ['days_present' => 22, 'overtime_hours' => 0, 'leave_days' => 0];
        $overtimeAttendance  = ['days_present' => 22, 'overtime_hours' => 8, 'leave_days' => 0];
        $missingAttendance   = null; // sengaja tidak ada record → anomali missing_attendance

        // ---- Periode T-2 (disbursed, tidak ada anomali) -----------------
        $p1 = Payroll::firstOrCreate(
            ['company_id' => $company->id, 'period' => now()->subMonths(2)->format('Y-m')],
            [
                'period_label'    => now()->subMonths(2)->locale('id')->translatedFormat('F Y'),
                'status'          => 'disbursed',
                'employee_count'  => $activeEmployees->count(),
                'gross_total'     => 0,
                'deduction_total' => 0,
                'net_total'       => 0,
                'anomaly_count'   => 0,
                'submitted_by'    => $hrUser->id,
                'approved_by'     => $financeUser->id,
                'approved_at'     => now()->subMonths(2)->setDay(27),
                'disbursed_by'    => $financeUser->id,
                'disbursed_at'    => now()->subMonths(2)->setDay(30),
            ]
        );

        if ($p1->payrollItems()->count() === 0) {
            $this->seedPayrollItems(
                $p1, $company->id, $activeEmployees, $service,
                attendanceOverrides: [
                    'DEMO-0001' => $overtimeAttendance,
                    'DEMO-0002' => $normalAttendance,
                    'DEMO-0003' => $normalAttendance,
                    'DEMO-0004' => $normalAttendance,
                    'DEMO-0005' => $overtimeAttendance,
                    'DEMO-0008' => $normalAttendance,
                ],
                itemStatus: 'transferred',
                disbursedAt: now()->subMonths(2)->setDay(30),
            );
        }

        // ---- Periode T-1 (pending_approval, anomali sudah acknowledged) --
        $p2 = Payroll::firstOrCreate(
            ['company_id' => $company->id, 'period' => now()->subMonth()->format('Y-m')],
            [
                'period_label'    => now()->subMonth()->locale('id')->translatedFormat('F Y'),
                'status'          => 'pending_approval',
                'employee_count'  => $activeEmployees->count(),
                'gross_total'     => 0,
                'deduction_total' => 0,
                'net_total'       => 0,
                'anomaly_count'   => 0,
                'submitted_by'    => $hrUser->id,
            ]
        );

        if ($p2->payrollItems()->count() === 0) {
            $this->seedPayrollItems(
                $p2, $company->id, $activeEmployees, $service,
                attendanceOverrides: [
                    'DEMO-0001' => $normalAttendance,
                    'DEMO-0002' => $normalAttendance,
                    'DEMO-0003' => $overtimeAttendance,
                    'DEMO-0004' => $normalAttendance,
                    'DEMO-0005' => $normalAttendance,
                    'DEMO-0008' => $missingAttendance, // anomali: missing_attendance
                ],
                // DEMO-0008 juga punya rejected bank → 2 anomali, tapi sudah di-acknowledge
                anomalyAcknowledged: ['DEMO-0008'],
            );
        }

        // ---- Periode bulan ini (needs_review, ada 2 anomali belum diack) --
        $p3 = Payroll::firstOrCreate(
            ['company_id' => $company->id, 'period' => now()->format('Y-m')],
            [
                'period_label'    => now()->locale('id')->translatedFormat('F Y'),
                'status'          => 'needs_review',
                'employee_count'  => $activeEmployees->count(),
                'gross_total'     => 0,
                'deduction_total' => 0,
                'net_total'       => 0,
                'anomaly_count'   => 2,
            ]
        );

        if ($p3->payrollItems()->count() === 0) {
            $this->seedPayrollItems(
                $p3, $company->id, $activeEmployees, $service,
                attendanceOverrides: [
                    'DEMO-0001' => $normalAttendance,
                    'DEMO-0002' => $normalAttendance,
                    'DEMO-0003' => $normalAttendance,
                    'DEMO-0004' => $overtimeAttendance,
                    'DEMO-0005' => $normalAttendance,
                    'DEMO-0008' => $missingAttendance, // anomali: missing_attendance + unverified_bank
                ],
                // tidak ada yang di-acknowledge → anomaly_count tetap 2
            );
        }
    }

    /**
     * Seed AttendanceRecords dan PayrollItems untuk satu payroll period.
     *
     * @param  \Illuminate\Support\Collection<string, Employee>  $activeEmployees  keyed by NIP
     * @param  array<string, array{days_present:int,overtime_hours:int,leave_days:int}|null>  $attendanceOverrides  NIP → data kehadiran atau null untuk missing
     * @param  list<string>  $anomalyAcknowledged  daftar NIP yang anomalinya sudah di-acknowledge
     */
    private function seedPayrollItems(
        Payroll $payroll,
        int $companyId,
        \Illuminate\Support\Collection $activeEmployees,
        PayrollService $service,
        array $attendanceOverrides = [],
        string $itemStatus = 'pending',
        ?\Illuminate\Support\Carbon $disbursedAt = null,
        array $anomalyAcknowledged = [],
    ): void {
        $gross = $deductions = $net = 0;
        $anomalyCount = 0;

        foreach ($activeEmployees as $nip => $employee) {
            $attendanceData = $attendanceOverrides[$nip] ?? ['days_present' => 22, 'overtime_hours' => 0, 'leave_days' => 0];

            // Buat AttendanceRecord jika ada data kehadiran (null = missing)
            $attendanceRecord = null;
            if ($attendanceData !== null) {
                $attendanceRecord = AttendanceRecord::firstOrCreate(
                    ['payroll_id' => $payroll->id, 'employee_id' => $employee->id],
                    [
                        'company_id'     => $companyId,
                        'days_present'   => $attendanceData['days_present'],
                        'overtime_hours' => $attendanceData['overtime_hours'],
                        'leave_days'     => $attendanceData['leave_days'],
                    ]
                );
            }

            // Hitung komponen gaji menggunakan formula PayrollService
            $components = $service->calculateItemForEmployee($employee, $attendanceRecord);

            // Deteksi anomali
            $anomalyTypes = [];
            if (! $employee->bank_account_number && ! $employee->bank_name) {
                $anomalyTypes[] = 'no_bank_account';
            }
            if (in_array($employee->bank_account_status, ['unverified', 'rejected'], true)) {
                $anomalyTypes[] = 'unverified_bank';
            }
            if ((float) $components['net_pay'] === 0.0) {
                $anomalyTypes[] = 'zero_net_pay';
            }
            if ($attendanceData === null) {
                $anomalyTypes[] = 'missing_attendance';
            }

            $hasAnomaly    = $anomalyTypes !== [];
            $acknowledged  = $hasAnomaly && in_array($nip, $anomalyAcknowledged, true);

            if ($hasAnomaly && ! $acknowledged) {
                $anomalyCount++;
            }

            PayrollItem::firstOrCreate(
                ['payroll_id' => $payroll->id, 'employee_id' => $employee->id],
                array_merge($components, [
                    'company_id'           => $companyId,
                    'status'               => $itemStatus,
                    'disbursed_at'         => $itemStatus === 'transferred' ? $disbursedAt : null,
                    'has_anomaly'          => $hasAnomaly,
                    'anomaly_type'         => $hasAnomaly ? json_encode($anomalyTypes) : null,
                    'anomaly_acknowledged' => $acknowledged,
                ])
            );

            $gross      += $components['gross_pay'];
            $deductions += $components['total_deduction'];
            $net        += $components['net_pay'];
        }

        // Update aggregate di Payroll
        $payroll->update([
            'employee_count'  => $activeEmployees->count(),
            'gross_total'     => round($gross, 2),
            'deduction_total' => round($deductions, 2),
            'net_total'       => round($net, 2),
            'anomaly_count'   => $anomalyCount,
        ]);
    }
}
