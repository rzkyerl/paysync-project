<?php

namespace Tests\Unit;

use App\Services\ImportResult;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests untuk value object ImportResult.
 */
class ImportResultTest extends TestCase
{
    public function test_successful_returns_true_when_no_errors(): void
    {
        $result = new ImportResult(imported: 5, errors: []);

        $this->assertTrue($result->successful());
    }

    public function test_successful_returns_false_when_errors_exist(): void
    {
        $result = new ImportResult(
            imported: 0,
            errors: [['row' => 2, 'field' => 'nip', 'message' => 'NIP wajib diisi.']]
        );

        $this->assertFalse($result->successful());
    }

    public function test_default_imported_is_zero(): void
    {
        $result = new ImportResult();

        $this->assertSame(0, $result->imported);
    }

    public function test_default_errors_is_empty_array(): void
    {
        $result = new ImportResult();

        $this->assertSame([], $result->errors);
    }

    public function test_imported_count_is_stored_correctly(): void
    {
        $result = new ImportResult(imported: 42);

        $this->assertSame(42, $result->imported);
    }

    public function test_errors_array_is_stored_correctly(): void
    {
        $errors = [
            ['row' => 2, 'field' => 'nip', 'message' => 'NIP wajib diisi.'],
            ['row' => 3, 'field' => 'name', 'message' => 'Nama wajib diisi.'],
        ];

        $result = new ImportResult(errors: $errors);

        $this->assertSame($errors, $result->errors);
    }

    public function test_properties_are_readonly(): void
    {
        $result = new ImportResult(imported: 10, errors: []);

        $this->assertSame(10, $result->imported);
        $this->assertSame([], $result->errors);
    }
}
