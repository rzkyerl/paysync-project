<?php

namespace Tests\Unit;

use App\Models\Employee;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests untuk Employee model.
 */
class EmployeeModelTest extends TestCase
{
    public function test_formatted_salary_returns_correct_format(): void
    {
        $employee = new Employee(['basic_salary' => 5_000_000]);

        $this->assertSame('Rp 5.000.000,00', $employee->formatted_salary);
    }

    public function test_formatted_salary_with_decimal(): void
    {
        $employee = new Employee(['basic_salary' => 4_500_000.50]);

        $this->assertSame('Rp 4.500.000,50', $employee->formatted_salary);
    }

    public function test_formatted_salary_with_zero(): void
    {
        $employee = new Employee(['basic_salary' => 0]);

        $this->assertSame('Rp 0,00', $employee->formatted_salary);
    }

    public function test_work_status_is_a_string_cast(): void
    {
        $employee = new Employee(['work_status' => 'active']);

        $this->assertSame('active', $employee->work_status);
    }

    public function test_fillable_includes_required_fields(): void
    {
        $employee = new Employee();
        $fillable = $employee->getFillable();

        foreach (['company_id', 'nip', 'name', 'department', 'position', 'work_status', 'join_date', 'basic_salary'] as $field) {
            $this->assertContains($field, $fillable, "Field '{$field}' should be fillable");
        }
    }
}
