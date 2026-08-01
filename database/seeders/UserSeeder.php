<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seed akun login untuk development/testing dalam satu tim.
 *
 * Semua akun berada di perusahaan yang sama: "PT PaySync Dev"
 * sehingga setelah migrate:fresh --seed langsung bisa dipakai tanpa onboarding.
 *
 * Yang di-seed:
 *   - 1 company  : PT PaySync Dev
 *   - 5 karyawan : EMP-0001 … EMP-0005 (semua active, rekening verified)
 *   - 1 payroll  : periode bulan ini, status draft → siap di-import kehadiran
 *   - 4 user     : tiap role punya akun, employee@paysync.test terhubung ke EMP-0001
 *
 * Login:
 *   super_admin     → ceo@paysync.test      / password
 *   hr_manager      → hr@paysync.test       / password
 *   finance_manager → finance@paysync.test  / password
 *   employee        → employee@paysync.test / password
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Company ──────────────────────────────────────────────────
        $company = Company::firstOrCreate(
            ['name' => 'PT PaySync Dev'],
            [
                'is_demo'         => false,
                'industry'        => 'Technology',
                'size'            => '11-50',
                'payroll_cut_off' => 25,
                'pay_date'        => 30,
            ]
        );

        // ── 2. Karyawan ─────────────────────────────────────────────────
        $employeeDefs = [
            [
                'nip'                 => 'EMP-0001',
                'name'                => 'Andi Saputra',
                'department'          => 'Engineering',
                'position'            => 'Backend Developer',
                'work_status'         => 'active',
                'join_date'           => '2023-01-02',
                'bank_name'           => 'BCA',
                'bank_account_number' => '1234567890',
                'bank_account_status' => 'verified',
                'basic_salary'        => 12000000,
            ],
            [
                'nip'                 => 'EMP-0002',
                'name'                => 'Budi Hartono',
                'department'          => 'Engineering',
                'position'            => 'Frontend Developer',
                'work_status'         => 'active',
                'join_date'           => '2023-03-15',
                'bank_name'           => 'Mandiri',
                'bank_account_number' => '0987654321',
                'bank_account_status' => 'verified',
                'basic_salary'        => 11000000,
            ],
            [
                'nip'                 => 'EMP-0003',
                'name'                => 'Citra Dewi',
                'department'          => 'People',
                'position'            => 'HR Staff',
                'work_status'         => 'active',
                'join_date'           => '2023-06-01',
                'bank_name'           => 'BNI',
                'bank_account_number' => '1122334455',
                'bank_account_status' => 'verified',
                'basic_salary'        => 9000000,
            ],
            [
                'nip'                 => 'EMP-0004',
                'name'                => 'Dian Permata',
                'department'          => 'Finance',
                'position'            => 'Finance Staff',
                'work_status'         => 'active',
                'join_date'           => '2024-01-10',
                'bank_name'           => 'BRI',
                'bank_account_number' => '5566778899',
                'bank_account_status' => 'verified',
                'basic_salary'        => 9500000,
            ],
            [
                'nip'                 => 'EMP-0005',
                'name'                => 'Eko Prasetyo',
                'department'          => 'Operations',
                'position'            => 'Operations Staff',
                'work_status'         => 'active',
                'join_date'           => '2024-07-01',
                'bank_name'           => 'CIMB Niaga',
                'bank_account_number' => '6677889900',
                'bank_account_status' => 'unverified',
                'basic_salary'        => 8000000,
            ],
        ];

        $employees = [];
        foreach ($employeeDefs as $def) {
            $employees[$def['nip']] = Employee::withTrashed()->updateOrCreate(
                ['nip' => $def['nip']],
                array_merge($def, [
                    'company_id' => $company->id,
                    'deleted_at' => null,
                ])
            );
        }

        // ── 3. Payroll draft bulan ini ───────────────────────────────────
        // Status draft → HR bisa langsung import kehadiran via /app/attendance
        $activeCount = collect($employees)
            ->filter(fn (Employee $e) => $e->work_status === 'active')
            ->count();

        Payroll::firstOrCreate(
            [
                'company_id' => $company->id,
                'period'     => now()->format('Y-m'),
            ],
            [
                'period_label'    => now()->locale('id')->translatedFormat('F Y'),
                'status'          => 'draft',
                'employee_count'  => $activeCount,
                'gross_total'     => 0,
                'deduction_total' => 0,
                'net_total'       => 0,
                'anomaly_count'   => 0,
            ]
        );

        // ── 4. Users ─────────────────────────────────────────────────────
        // employee@paysync.test terhubung ke EMP-0001 agar bisa lihat payslip
        $userDefs = [
            [
                'name'        => 'Ahmad Fauzi',
                'email'       => 'ceo@paysync.test',
                'role'        => 'super_admin',
                'employee_id' => null,
            ],
            [
                'name'        => 'HR Manager',
                'email'       => 'hr@paysync.test',
                'role'        => 'hr_manager',
                'employee_id' => null,
            ],
            [
                'name'        => 'Finance Manager',
                'email'       => 'finance@paysync.test',
                'role'        => 'finance_manager',
                'employee_id' => null,
            ],
            [
                'name'        => 'Karyawan',
                'email'       => 'employee@paysync.test',
                'role'        => 'employee',
                'employee_id' => $employees['EMP-0001']->id,
            ],
        ];

        foreach ($userDefs as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'password'          => Hash::make('password'),
                    'role'              => $data['role'],
                    'is_demo'           => false,
                    'company_id'        => $company->id,
                    'employee_id'       => $data['employee_id'],
                    'email_verified_at' => now(),
                    'status'            => 'active',
                ]
            );
        }
    }
}
