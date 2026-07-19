<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Feature tests for EmployeeController::index().
 *
 * Covers:
 *   - Search by name / NIP
 *   - Filter by department / work_status
 *   - Sort whitelist (valid columns work; invalid column falls back to default)
 *   - Pagination: per_page param respected, out-of-bounds page redirects to page 1
 *
 * All tests authenticate as an hr_manager user.
 */
class EmployeeControllerIndexTest extends TestCase
{
    use RefreshDatabase;

    /** The HR manager user used across all tests. */
    private User $hrManager;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the hr_manager user (no employee link required for index access)
        $this->hrManager = User::create([
            'name'              => 'HR Manager Test',
            'email'             => 'hr-test@example.com',
            'password'          => Hash::make('password'),
            'role'              => 'hr_manager',
            'email_verified_at' => now(),
        ]);
    }

    // -----------------------------------------------------------------------
    // Helper
    // -----------------------------------------------------------------------

    /**
     * Create a minimal Employee with only the required fields.
     */
    private function makeEmployee(array $overrides = []): Employee
    {
        return Employee::create(array_merge([
            'nip'                 => 'EMP-' . str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT),
            'name'                => 'Test Employee',
            'department'          => 'Engineering',
            'position'            => 'Developer',
            'work_status'         => 'active',
            'join_date'           => '2022-01-01',
            'bank_account_status' => 'verified',
        ], $overrides));
    }

    // -----------------------------------------------------------------------
    // Search Tests
    // -----------------------------------------------------------------------

    public function test_search_by_name_returns_matching_employees(): void
    {
        $this->makeEmployee(['name' => 'Budi Santoso',  'nip' => 'EMP-1001']);
        $this->makeEmployee(['name' => 'Siti Rahayu',   'nip' => 'EMP-1002']);
        $this->makeEmployee(['name' => 'Agus Prasetyo', 'nip' => 'EMP-1003']);

        $response = $this->actingAs($this->hrManager)
            ->get(route('employees.index', ['search' => 'Budi']));

        $response->assertStatus(200);

        // The view receives $employees — verify only Budi is in the result
        $employees = $response->viewData('employees');
        $this->assertCount(1, $employees);
        $this->assertEquals('Budi Santoso', $employees->first()->name);
    }

    public function test_search_by_name_non_matching_returns_empty(): void
    {
        $this->makeEmployee(['name' => 'Budi Santoso', 'nip' => 'EMP-2001']);

        $response = $this->actingAs($this->hrManager)
            ->get(route('employees.index', ['search' => 'ZzzNonExistent']));

        $response->assertStatus(200);
        $employees = $response->viewData('employees');
        $this->assertCount(0, $employees);
    }

    public function test_search_by_nip_returns_matching_employee(): void
    {
        $this->makeEmployee(['name' => 'Rina Dewi',   'nip' => 'EMP-3001']);
        $this->makeEmployee(['name' => 'Hendra Kusa', 'nip' => 'EMP-3002']);

        $response = $this->actingAs($this->hrManager)
            ->get(route('employees.index', ['search' => 'EMP-3001']));

        $response->assertStatus(200);
        $employees = $response->viewData('employees');
        $this->assertCount(1, $employees);
        $this->assertEquals('EMP-3001', $employees->first()->nip);
    }

    // -----------------------------------------------------------------------
    // Filter Tests
    // -----------------------------------------------------------------------

    public function test_filter_by_department_returns_only_matching_employees(): void
    {
        $this->makeEmployee(['name' => 'Alice', 'nip' => 'EMP-4001', 'department' => 'Engineering']);
        $this->makeEmployee(['name' => 'Bob',   'nip' => 'EMP-4002', 'department' => 'HR']);
        $this->makeEmployee(['name' => 'Carol', 'nip' => 'EMP-4003', 'department' => 'Engineering']);

        $response = $this->actingAs($this->hrManager)
            ->get(route('employees.index', ['department' => 'Engineering']));

        $response->assertStatus(200);
        $employees = $response->viewData('employees');
        $this->assertCount(2, $employees);
        $employees->each(fn ($e) => $this->assertEquals('Engineering', $e->department));
    }

    public function test_filter_by_work_status_returns_only_matching_employees(): void
    {
        $this->makeEmployee(['name' => 'Active1',    'nip' => 'EMP-5001', 'work_status' => 'active']);
        $this->makeEmployee(['name' => 'Probation1', 'nip' => 'EMP-5002', 'work_status' => 'probation']);
        $this->makeEmployee(['name' => 'Active2',    'nip' => 'EMP-5003', 'work_status' => 'active']);
        $this->makeEmployee(['name' => 'Contract1',  'nip' => 'EMP-5004', 'work_status' => 'contract']);

        $response = $this->actingAs($this->hrManager)
            ->get(route('employees.index', ['status' => 'active']));

        $response->assertStatus(200);
        $employees = $response->viewData('employees');
        $this->assertCount(2, $employees);
        $employees->each(fn ($e) => $this->assertEquals('active', $e->work_status));
    }

    // -----------------------------------------------------------------------
    // Sort Whitelist Tests
    // -----------------------------------------------------------------------

    public function test_valid_sort_column_name_asc_works_without_sql_error(): void
    {
        $this->makeEmployee(['name' => 'Charlie', 'nip' => 'EMP-6001']);
        $this->makeEmployee(['name' => 'Alice',   'nip' => 'EMP-6002']);

        $response = $this->actingAs($this->hrManager)
            ->get(route('employees.index', ['sort' => 'name', 'dir' => 'asc']));

        $response->assertStatus(200);

        $employees = $response->viewData('employees');
        // Sorted ASC by name: Alice first
        $this->assertEquals('Alice', $employees->first()->name);
    }

    public function test_valid_sort_column_nip_descending_works(): void
    {
        $this->makeEmployee(['name' => 'Charlie', 'nip' => 'EMP-7001']);
        $this->makeEmployee(['name' => 'Alice',   'nip' => 'EMP-7002']);

        $response = $this->actingAs($this->hrManager)
            ->get(route('employees.index', ['sort' => 'nip', 'dir' => 'desc']));

        $response->assertStatus(200);
        $employees = $response->viewData('employees');
        // Sorted DESC by nip: EMP-7002 first
        $this->assertEquals('EMP-7002', $employees->first()->nip);
    }

    public function test_invalid_sort_column_falls_back_to_default_without_sql_error(): void
    {
        $this->makeEmployee(['name' => 'D Employee', 'nip' => 'EMP-8001']);
        $this->makeEmployee(['name' => 'A Employee', 'nip' => 'EMP-8002']);

        // Inject an invalid/potentially malicious column name
        $response = $this->actingAs($this->hrManager)
            ->get(route('employees.index', [
                'sort' => 'password; DROP TABLE employees;--',
                'dir'  => 'asc',
            ]));

        // Should succeed (no 500 or SQL error) and fall back to default sort (name)
        $response->assertStatus(200);

        $sortBy = $response->viewData('sortBy');
        $this->assertEquals('name', $sortBy);

        $employees = $response->viewData('employees');
        // Sorted by name ASC: A Employee first
        $this->assertEquals('A Employee', $employees->first()->name);
    }

    public function test_invalid_dir_falls_back_to_asc(): void
    {
        $this->makeEmployee(['name' => 'Test', 'nip' => 'EMP-9001']);

        $response = $this->actingAs($this->hrManager)
            ->get(route('employees.index', ['sort' => 'name', 'dir' => 'INVALID']));

        $response->assertStatus(200);
        $sortDir = $response->viewData('sortDir');
        $this->assertEquals('asc', $sortDir);
    }

    // -----------------------------------------------------------------------
    // Pagination Tests
    // -----------------------------------------------------------------------

    public function test_per_page_param_is_respected(): void
    {
        // Create enough employees to exceed the requested page size.
        for ($i = 1; $i <= 40; $i++) {
            $this->makeEmployee([
                'nip'  => 'EMP-' . str_pad((string) (10000 + $i), 5, '0', STR_PAD_LEFT),
                'name' => 'Employee ' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
            ]);
        }

        // Request one of the supported per-page values (15/30/50).
        $response = $this->actingAs($this->hrManager)
            ->get(route('employees.index', ['per_page' => 30]));

        $response->assertStatus(200);
        $employees = $response->viewData('employees');
        $this->assertEquals(30, $employees->perPage());
        $this->assertCount(30, $employees->items());
        $this->assertEquals(40, $employees->total());
    }

    public function test_invalid_per_page_falls_back_to_default_15(): void
    {
        // Create 20 employees
        for ($i = 1; $i <= 20; $i++) {
            $this->makeEmployee([
                'nip'  => 'EMP-' . str_pad((string) (20000 + $i), 5, '0', STR_PAD_LEFT),
                'name' => 'Pagination Employee ' . $i,
            ]);
        }

        $response = $this->actingAs($this->hrManager)
            ->get(route('employees.index', ['per_page' => 999]));

        $response->assertStatus(200);
        $employees = $response->viewData('employees');
        $this->assertEquals(15, $employees->perPage());
    }

    public function test_page_out_of_bounds_redirects_to_page_1(): void
    {
        // Only 2 employees — page 99 is far out of bounds
        $this->makeEmployee(['nip' => 'EMP-30001', 'name' => 'Emp One']);
        $this->makeEmployee(['nip' => 'EMP-30002', 'name' => 'Emp Two']);

        $response = $this->actingAs($this->hrManager)
            ->get(route('employees.index', ['page' => 99, 'per_page' => 15]));

        // Expect a redirect back to page 1
        $response->assertRedirect();
        $this->assertStringContainsString('page=1', $response->headers->get('Location'));
    }
}
