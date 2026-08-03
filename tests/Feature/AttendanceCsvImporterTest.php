<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Payroll;
use App\Services\AttendanceCsvImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Feature tests untuk AttendanceCsvImporter service.
 */
class AttendanceCsvImporterTest extends TestCase
{
    use RefreshDatabase;

    private AttendanceCsvImporter $importer;
    private Company $company;
    private Payroll $payroll;
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importer = new AttendanceCsvImporter();

        $this->company = Company::create(['name' => 'Acme Corp']);

        $this->employee = Employee::create([
            'company_id'   => $this->company->id,
            'nip'          => 'EMP-0001',
            'name'         => 'Budi Santoso',
            'department'   => 'Engineering',
            'position'     => 'Developer',
            'work_status'  => 'active',
            'join_date'    => '2022-01-01',
            'basic_salary' => 5_000_000,
        ]);

        $this->payroll = Payroll::create([
            'company_id'     => $this->company->id,
            'period'         => '2026-07',
            'period_label'   => 'Juli 2026',
            'status'         => 'draft',
            'employee_count' => 0,
            'gross_total'    => 0,
            'deduction_total'=> 0,
            'net_total'      => 0,
        ]);
    }

    // -----------------------------------------------------------------------
    // Helper
    // -----------------------------------------------------------------------

    private function makeCsvFile(string $content): UploadedFile
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'att_csv_');
        file_put_contents($tmpPath, $content);

        return new UploadedFile($tmpPath, 'attendance.csv', 'text/csv', null, true);
    }

    // -----------------------------------------------------------------------
    // Happy path
    // -----------------------------------------------------------------------

    public function test_valid_csv_creates_attendance_records(): void
    {
        $content = "nip,work_days,days_present,overtime_hours,leave_days\n"
            . "EMP-0001,22,20,3,1\n";
        $file   = $this->makeCsvFile($content);

        $result = $this->importer->import($file, $this->payroll);

        $this->assertTrue($result->successful());
        $this->assertSame(1, $result->imported);
        $this->assertDatabaseHas('attendance_records', [
            'payroll_id'     => $this->payroll->id,
            'employee_id'    => $this->employee->id,
            'work_days'      => 22,
            'days_present'   => 20,
            'leave_days'     => 1,
        ]);
    }

    public function test_existing_record_for_same_payroll_employee_is_updated(): void
    {
        // Pre-create attendance record
        AttendanceRecord::create([
            'payroll_id'     => $this->payroll->id,
            'employee_id'    => $this->employee->id,
            'company_id'     => $this->company->id,
            'work_days'      => 22,
            'days_present'   => 15,
            'overtime_hours' => 0,
            'leave_days'     => 0,
        ]);

        $content = "nip,work_days,days_present,overtime_hours,leave_days\n"
            . "EMP-0001,22,20,5,2\n";
        $file = $this->makeCsvFile($content);

        $result = $this->importer->import($file, $this->payroll);

        $this->assertTrue($result->successful());
        $this->assertDatabaseCount('attendance_records', 1); // tidak duplikat
        $this->assertDatabaseHas('attendance_records', [
            'payroll_id'   => $this->payroll->id,
            'days_present' => 20,
        ]);
    }

    // -----------------------------------------------------------------------
    // Optional work_days column
    // -----------------------------------------------------------------------

    public function test_missing_work_days_column_defaults_to_22(): void
    {
        $content = "nip,days_present,overtime_hours,leave_days\n"
            . "EMP-0001,20,0,0\n";
        $file = $this->makeCsvFile($content);

        $result = $this->importer->import($file, $this->payroll);

        $this->assertTrue($result->successful());
        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $this->employee->id,
            'work_days'   => 22,
        ]);
    }

    // -----------------------------------------------------------------------
    // Missing required headers
    // -----------------------------------------------------------------------

    public function test_missing_required_header_returns_error(): void
    {
        $content = "nip,days_present,leave_days\n" // hilang overtime_hours
            . "EMP-0001,20,0\n";
        $file = $this->makeCsvFile($content);

        $result = $this->importer->import($file, $this->payroll);

        $this->assertFalse($result->successful());
        $this->assertStringContainsString('overtime_hours', $result->errors[0]['message']);
    }

    // -----------------------------------------------------------------------
    // Empty file
    // -----------------------------------------------------------------------

    public function test_empty_file_returns_error(): void
    {
        $file   = $this->makeCsvFile('');
        $result = $this->importer->import($file, $this->payroll);

        $this->assertFalse($result->successful());
    }

    // -----------------------------------------------------------------------
    // NIP not found in company
    // -----------------------------------------------------------------------

    public function test_nip_not_found_in_company_returns_error(): void
    {
        $content = "nip,days_present,overtime_hours,leave_days\n"
            . "EMP-9999,20,0,0\n"; // NIP tidak ada di company
        $file = $this->makeCsvFile($content);

        $result = $this->importer->import($file, $this->payroll);

        $this->assertFalse($result->successful());
        $nipErrors = array_filter($result->errors, fn ($e) => $e['field'] === 'nip');
        $this->assertNotEmpty($nipErrors);
    }

    // -----------------------------------------------------------------------
    // Numeric validation
    // -----------------------------------------------------------------------

    public function test_negative_days_present_returns_error(): void
    {
        $content = "nip,days_present,overtime_hours,leave_days\n"
            . "EMP-0001,-1,0,0\n";
        $file = $this->makeCsvFile($content);

        $result = $this->importer->import($file, $this->payroll);

        $this->assertFalse($result->successful());
        $errors = array_filter($result->errors, fn ($e) => $e['field'] === 'days_present');
        $this->assertNotEmpty($errors);
    }

    public function test_non_numeric_overtime_returns_error(): void
    {
        $content = "nip,days_present,overtime_hours,leave_days\n"
            . "EMP-0001,20,abc,0\n";
        $file = $this->makeCsvFile($content);

        $result = $this->importer->import($file, $this->payroll);

        $this->assertFalse($result->successful());
        $errors = array_filter($result->errors, fn ($e) => $e['field'] === 'overtime_hours');
        $this->assertNotEmpty($errors);
    }

    // -----------------------------------------------------------------------
    // work_days out of range
    // -----------------------------------------------------------------------

    public function test_work_days_zero_returns_error(): void
    {
        $content = "nip,work_days,days_present,overtime_hours,leave_days\n"
            . "EMP-0001,0,20,0,0\n";
        $file = $this->makeCsvFile($content);

        $result = $this->importer->import($file, $this->payroll);

        $this->assertFalse($result->successful());
        $errors = array_filter($result->errors, fn ($e) => $e['field'] === 'work_days');
        $this->assertNotEmpty($errors);
    }

    public function test_work_days_32_returns_error(): void
    {
        $content = "nip,work_days,days_present,overtime_hours,leave_days\n"
            . "EMP-0001,32,20,0,0\n";
        $file = $this->makeCsvFile($content);

        $result = $this->importer->import($file, $this->payroll);

        $this->assertFalse($result->successful());
        $errors = array_filter($result->errors, fn ($e) => $e['field'] === 'work_days');
        $this->assertNotEmpty($errors);
    }

    public function test_work_days_valid_range_1_to_31_is_accepted(): void
    {
        $content = "nip,work_days,days_present,overtime_hours,leave_days\n"
            . "EMP-0001,31,20,0,0\n";
        $file = $this->makeCsvFile($content);

        $result = $this->importer->import($file, $this->payroll);

        $this->assertTrue($result->successful());
    }

    // -----------------------------------------------------------------------
    // Empty rows skipped
    // -----------------------------------------------------------------------

    public function test_empty_rows_are_skipped_gracefully(): void
    {
        $content = "nip,days_present,overtime_hours,leave_days\n"
            . "EMP-0001,20,0,0\n"
            . "\n"
            . "\n";
        $file = $this->makeCsvFile($content);

        $result = $this->importer->import($file, $this->payroll);

        $this->assertTrue($result->successful());
        $this->assertSame(1, $result->imported);
    }

    // -----------------------------------------------------------------------
    // BOM stripped
    // -----------------------------------------------------------------------

    public function test_bom_stripped_from_headers(): void
    {
        $bom     = "\xEF\xBB\xBF";
        $content = $bom . "nip,days_present,overtime_hours,leave_days\n"
            . "EMP-0001,20,0,0\n";
        $file = $this->makeCsvFile($content);

        $result = $this->importer->import($file, $this->payroll);

        $this->assertTrue($result->successful(), 'BOM harus di-strip dari header pertama');
    }
}
