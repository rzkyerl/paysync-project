<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class EmployeeImportDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_imported_employees_are_visible_on_the_hr_dashboard(): void
    {
        $company = Company::create(['name' => 'PT Import Test']);
        $hrManager = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'hr_manager',
            'is_demo' => false,
        ]);

        $rows = [
            'nip,name,department,position,work_status,join_date,basic_salary,bank_name,bank_account_number',
        ];

        foreach (range(1, 7) as $number) {
            $rows[] = implode(',', [
                sprintf('EMP-%04d', $number),
                'Pegawai Import '.$number,
                'Engineering',
                'Developer',
                'active',
                '2026-01-01',
                '5000000',
                'BCA',
                '12345678'.$number,
            ]);
        }

        $file = UploadedFile::fake()->createWithContent(
            'employees.csv',
            implode("\n", $rows),
        );

        $this->actingAs($hrManager)
            ->post(route('employees.import.store'), ['file' => $file])
            ->assertRedirect(route('dashboard.hr'));

        $this->assertDatabaseCount('employees', 7);

        // Verifikasi semua karyawan tersimpan dan terlihat di halaman employees.index
        $employeesPage = $this->actingAs($hrManager)
            ->get(route('employees.index'))
            ->assertOk();

        foreach (range(1, 7) as $number) {
            $employeesPage->assertSee('Pegawai Import '.$number);
        }
    }
}
