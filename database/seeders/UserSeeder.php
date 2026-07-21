<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seed akun login untuk testing — tanpa data dummy perusahaan.
 * Setelah login, lakukan onboarding via UI untuk membuat company & data nyata.
 *
 * Akun tersedia:
 *   super_admin     → admin@paysync.test      / password
 *   hr_manager      → hr@paysync.test         / password
 *   finance_manager → finance@paysync.test    / password
 *   employee        → employee@paysync.test   / password
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'              => 'Ahmad Fauzi',
                'email'             => 'ceo@paysync.test',
                'role'              => 'super_admin',
            ],
            [
                'name'              => 'HR Manager',
                'email'             => 'hr@paysync.test',
                'role'              => 'hr_manager',
            ],
            [
                'name'              => 'Finance Manager',
                'email'             => 'finance@paysync.test',
                'role'              => 'finance_manager',
            ],
            [
                'name'              => 'Karyawan',
                'email'             => 'employee@paysync.test',
                'role'              => 'employee',
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'password'          => Hash::make('password'),
                    'role'              => $data['role'],
                    'is_demo'           => false,
                    'company_id'        => null,
                    'employee_id'       => null,
                    'email_verified_at' => now(),
                    'status'            => 'active',
                ]
            );
        }
    }
}
