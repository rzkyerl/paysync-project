<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests untuk EmployeeController — CRUD, verifikasi bank, dan import.
 */
class EmployeeControllerCrudTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $hrManager;
    private User $financeManager;

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
    }

    private function validEmployeePayload(array $overrides = []): array
    {
        return array_merge([
            'nip'                => 'EMP-0001',
            'name'               => 'Budi Santoso',
            'department'         => 'Engineering',
            'position'           => 'Developer',
            'work_status'        => 'active',
            'join_date'          => '2022-01-15',
            'basic_salary'       => 5000000,
            'bank_account_status'=> 'verified',
            'bank_name'          => 'BCA',
            'bank_account_number'=> '1234567890',
        ], $overrides);
    }

    private function makeEmployee(array $overrides = []): Employee
    {
        return Employee::create(array_merge([
            'company_id'   => $this->company->id,
            'nip'          => 'EMP-0001',
            'name'         => 'Budi',
            'department'   => 'IT',
            'position'     => 'Dev',
            'work_status'  => 'active',
            'join_date'    => '2022-01-01',
            'basic_salary' => 5_000_000,
        ], $overrides));
    }

    // -----------------------------------------------------------------------
    // store
    // -----------------------------------------------------------------------

    public function test_hr_manager_can_create_employee(): void
    {
        $this->actingAs($this->hrManager)
            ->post(route('employees.store'), $this->validEmployeePayload())
            ->assertRedirect(route('employees.index'));

        $this->assertDatabaseHas('employees', [
            'nip'        => 'EMP-0001',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_store_fails_with_invalid_nip_format(): void
    {
        $this->actingAs($this->hrManager)
            ->post(route('employees.store'), $this->validEmployeePayload(['nip' => 'INVALID-NIP']))
            ->assertSessionHasErrors('nip');
    }

    public function test_store_fails_with_duplicate_nip(): void
    {
        $this->makeEmployee();

        $this->actingAs($this->hrManager)
            ->post(route('employees.store'), $this->validEmployeePayload(['nip' => 'EMP-0001']))
            ->assertSessionHasErrors('nip');
    }

    public function test_non_hr_manager_cannot_create_employee(): void
    {
        $this->actingAs($this->financeManager)
            ->post(route('employees.store'), $this->validEmployeePayload())
            ->assertForbidden();
    }

    public function test_store_requires_name(): void
    {
        $this->actingAs($this->hrManager)
            ->post(route('employees.store'), $this->validEmployeePayload(['name' => '']))
            ->assertSessionHasErrors('name');
    }

    public function test_store_requires_valid_work_status(): void
    {
        $this->actingAs($this->hrManager)
            ->post(route('employees.store'), $this->validEmployeePayload(['work_status' => 'sleeping']))
            ->assertSessionHasErrors('work_status');
    }

    // -----------------------------------------------------------------------
    // update
    // -----------------------------------------------------------------------

    public function test_hr_manager_can_update_employee(): void
    {
        $employee = $this->makeEmployee();

        $this->actingAs($this->hrManager)
            ->put(route('employees.update', $employee), $this->validEmployeePayload([
                'nip'  => 'EMP-0001',
                'name' => 'Updated Name',
            ]))->assertRedirect(route('employees.index'));

        $this->assertDatabaseHas('employees', ['name' => 'Updated Name']);
    }

    public function test_changing_bank_account_resets_status_to_unverified(): void
    {
        $employee = $this->makeEmployee([
            'bank_name'           => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_status' => 'verified',
        ]);

        $this->actingAs($this->hrManager)
            ->put(route('employees.update', $employee), $this->validEmployeePayload([
                'nip'                 => 'EMP-0001',
                'bank_account_number' => '9999999999', // nomor berubah
                'bank_account_status' => 'verified',   // coba set verified
            ]));

        $this->assertDatabaseHas('employees', [
            'id'                  => $employee->id,
            'bank_account_status' => 'unverified', // harus direset
        ]);
    }

    public function test_non_hr_cannot_update_employee(): void
    {
        $employee = $this->makeEmployee();

        $this->actingAs($this->financeManager)
            ->put(route('employees.update', $employee), $this->validEmployeePayload())
            ->assertForbidden();
    }

    public function test_cannot_update_employee_from_different_company(): void
    {
        $otherCompany  = Company::create(['name' => 'Other Corp']);
        $otherEmployee = Employee::create([
            'company_id'   => $otherCompany->id,
            'nip'          => 'EMP-9999',
            'name'         => 'Other Employee',
            'department'   => 'IT',
            'position'     => 'Dev',
            'work_status'  => 'active',
            'join_date'    => '2022-01-01',
            'basic_salary' => 5_000_000,
        ]);

        $this->actingAs($this->hrManager)
            ->put(route('employees.update', $otherEmployee), $this->validEmployeePayload(['nip' => 'EMP-9999']))
            ->assertForbidden();
    }

    // -----------------------------------------------------------------------
    // destroy
    // -----------------------------------------------------------------------

    public function test_hr_manager_can_soft_delete_employee(): void
    {
        $employee = $this->makeEmployee();

        $this->actingAs($this->hrManager)
            ->delete(route('employees.destroy', $employee))
            ->assertRedirect();

        $this->assertSoftDeleted('employees', ['id' => $employee->id]);
    }

    public function test_non_hr_cannot_delete_employee(): void
    {
        $employee = $this->makeEmployee();

        $this->actingAs($this->financeManager)
            ->delete(route('employees.destroy', $employee))
            ->assertForbidden();
    }

    // -----------------------------------------------------------------------
    // verifyBank / rejectBank
    // -----------------------------------------------------------------------

    public function test_hr_manager_can_verify_bank_account(): void
    {
        $employee = $this->makeEmployee(['bank_account_status' => 'unverified']);

        $this->actingAs($this->hrManager)
            ->post(route('employees.verify-bank', $employee))
            ->assertRedirect();

        $this->assertDatabaseHas('employees', [
            'id'                  => $employee->id,
            'bank_account_status' => 'verified',
        ]);
    }

    public function test_hr_manager_can_reject_bank_account(): void
    {
        $employee = $this->makeEmployee(['bank_account_status' => 'unverified']);

        $this->actingAs($this->hrManager)
            ->post(route('employees.reject-bank', $employee))
            ->assertRedirect();

        $this->assertDatabaseHas('employees', [
            'id'                  => $employee->id,
            'bank_account_status' => 'rejected',
        ]);
    }

    public function test_non_hr_cannot_verify_bank(): void
    {
        $employee = $this->makeEmployee(['bank_account_status' => 'unverified']);

        $this->actingAs($this->financeManager)
            ->post(route('employees.verify-bank', $employee))
            ->assertForbidden();
    }

    // -----------------------------------------------------------------------
    // show
    // -----------------------------------------------------------------------

    public function test_hr_manager_can_view_employee_detail(): void
    {
        $employee = $this->makeEmployee();

        $this->actingAs($this->hrManager)
            ->get(route('employees.show', $employee))
            ->assertOk();
    }

    public function test_finance_manager_can_view_employee_detail(): void
    {
        $employee = $this->makeEmployee();

        $this->actingAs($this->financeManager)
            ->get(route('employees.show', $employee))
            ->assertOk();
    }

    // -----------------------------------------------------------------------
    // create page
    // -----------------------------------------------------------------------

    public function test_hr_manager_can_access_create_employee_form(): void
    {
        $this->actingAs($this->hrManager)
            ->get(route('employees.create'))
            ->assertOk();
    }

    public function test_finance_manager_cannot_access_create_form(): void
    {
        $this->actingAs($this->financeManager)
            ->get(route('employees.create'))
            ->assertForbidden();
    }

    // -----------------------------------------------------------------------
    // downloadTemplate
    // -----------------------------------------------------------------------

    public function test_hr_manager_can_download_import_template(): void
    {
        $this->actingAs($this->hrManager)
            ->get(route('employees.import.template'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_finance_manager_cannot_download_template(): void
    {
        // GET request dengan role salah → redirect ke dashboard (bukan 403)
        $this->actingAs($this->financeManager)
            ->get(route('employees.import.template'))
            ->assertRedirect('/app/dashboard-finance');
    }
}
