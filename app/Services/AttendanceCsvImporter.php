<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\Payroll;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class AttendanceCsvImporter
{
    public function import(UploadedFile $file, Payroll $payroll): ImportResult
    {
        $handle = fopen($file->getRealPath(), 'rb');
        if ($handle === false) {
            return new ImportResult(errors: [['row' => 1, 'field' => 'file', 'message' => 'File CSV tidak dapat dibaca.']]);
        }
        $headers = fgetcsv($handle);
        $expected = ['nip', 'days_present', 'overtime_hours', 'leave_days'];
        if (! is_array($headers)) {
            fclose($handle);

            return new ImportResult(errors: [['row' => 1, 'field' => 'file', 'message' => 'File CSV kosong.']]);
        }
        $headers = array_map(fn ($header) => strtolower(trim((string) preg_replace('/^\xEF\xBB\xBF/', '', $header))), $headers);
        $missing = array_diff($expected, $headers);
        if ($missing !== []) {
            fclose($handle);

            return new ImportResult(errors: [['row' => 1, 'field' => 'header', 'message' => 'Header wajib tidak ditemukan: '.implode(', ', $missing).'.']]);
        }

        $employees = $payroll->company->employees()->get()->keyBy('nip');
        $rows = [];
        $errors = [];
        $line = 1;
        while (($values = fgetcsv($handle)) !== false) {
            $line++;
            if ($values === [null] || count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }
            $row = array_fill_keys($expected, null);
            foreach ($headers as $index => $header) {
                if (array_key_exists($header, $row)) {
                    $row[$header] = trim((string) ($values[$index] ?? ''));
                }
            }
            $rows[] = ['line' => $line, 'data' => $row];
            if (! $employees->has($row['nip'])) {
                $errors[] = ['row' => $line, 'field' => 'nip', 'message' => 'NIP tidak ditemukan di perusahaan ini.'];
            }
            foreach (['days_present', 'overtime_hours', 'leave_days'] as $field) {
                if ($row[$field] === '' || ! is_numeric($row[$field]) || (float) $row[$field] < 0) {
                    $errors[] = ['row' => $line, 'field' => $field, 'message' => 'Nilai harus berupa angka nol atau lebih.'];
                }
            }
        }
        fclose($handle);
        if ($errors !== []) {
            return new ImportResult(errors: $errors);
        }

        DB::transaction(function () use ($rows, $employees, $payroll): void {
            foreach ($rows as $item) {
                $data = $item['data'];
                AttendanceRecord::updateOrCreate(
                    ['payroll_id' => $payroll->id, 'employee_id' => $employees[$data['nip']]->id],
                    ['company_id' => $payroll->company_id, 'days_present' => $data['days_present'], 'overtime_hours' => $data['overtime_hours'], 'leave_days' => $data['leave_days']],
                );
            }
        });

        return new ImportResult(imported: count($rows));
    }
}
