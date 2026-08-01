<?php

namespace App\Services;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class EmployeeCsvImporter
{
    private const HEADERS = ['nip', 'name', 'department', 'position', 'work_status', 'join_date', 'basic_salary', 'bank_name', 'bank_account_number'];

    private const STATUSES = ['active', 'probation', 'contract', 'inactive'];

    public function import(UploadedFile $file, int $companyId): ImportResult
    {
        $handle = fopen($file->getRealPath(), 'rb');
        if ($handle === false) {
            return new ImportResult(errors: [['row' => 1, 'field' => 'file', 'message' => 'File CSV tidak dapat dibaca.']]);
        }

        // Auto-detect delimiter: Excel Indonesia pakai ';', standar pakai ','
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return new ImportResult(errors: [['row' => 1, 'field' => 'file', 'message' => 'File CSV kosong.']]);
        }
        $semicolonCount = substr_count($firstLine, ';');
        $commaCount     = substr_count($firstLine, ',');
        $delimiter      = $semicolonCount > $commaCount ? ';' : ',';
        rewind($handle);

        $headers = fgetcsv($handle, 0, $delimiter);
        if (! is_array($headers)) {
            fclose($handle);

            return new ImportResult(errors: [['row' => 1, 'field' => 'file', 'message' => 'File CSV kosong.']]);
        }

        $headers = array_map(fn ($header) => strtolower(trim((string) preg_replace('/^\xEF\xBB\xBF/', '', $header))), $headers);
        $missingHeaders = array_diff(self::HEADERS, $headers);
        if ($missingHeaders !== []) {
            fclose($handle);

            return new ImportResult(errors: [['row' => 1, 'field' => 'header', 'message' => 'Header wajib tidak ditemukan: '.implode(', ', $missingHeaders).'.']]);
        }

        $rows = [];
        $errors = [];
        $seenNips = [];
        $line = 1;

        while (($values = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;
            if ($values === [null] || count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $row = array_fill_keys(self::HEADERS, null);
            foreach ($headers as $index => $header) {
                if (array_key_exists($header, $row)) {
                    $row[$header] = trim((string) ($values[$index] ?? ''));
                }
            }
            // Normalize work_status to lowercase so 'Active', 'ACTIVE', etc. are accepted
            if (isset($row['work_status'])) {
                $row['work_status'] = strtolower($row['work_status']);
            }
            $rows[] = ['line' => $line, 'data' => $row];

            $nip = $row['nip'];
            if ($nip === '') {
                $errors[] = ['row' => $line, 'field' => 'nip', 'message' => 'NIP wajib diisi.'];
            } elseif (isset($seenNips[$nip])) {
                $errors[] = ['row' => $line, 'field' => 'nip', 'message' => 'NIP duplikat di dalam file.'];
            } else {
                $seenNips[$nip] = true;
            }

            if ($row['name'] === '') {
                $errors[] = ['row' => $line, 'field' => 'name', 'message' => 'Nama wajib diisi.'];
            }
            if ($row['department'] === '') {
                $errors[] = ['row' => $line, 'field' => 'department', 'message' => 'Departemen wajib diisi.'];
            }
            if ($row['position'] === '') {
                $errors[] = ['row' => $line, 'field' => 'position', 'message' => 'Jabatan wajib diisi.'];
            }
            if (! in_array($row['work_status'], self::STATUSES, true)) {
                $errors[] = ['row' => $line, 'field' => 'work_status', 'message' => 'Status kerja tidak valid. Nilai yang diterima: active, probation, contract, inactive.'];
            }
            if ($row['basic_salary'] === '' || ! is_numeric($row['basic_salary']) || (float) $row['basic_salary'] < 0) {
                $errors[] = ['row' => $line, 'field' => 'basic_salary', 'message' => 'Gaji pokok harus berupa angka nol atau lebih.'];
            }
            if ($this->parseDate($row['join_date']) === null) {
                $errors[] = ['row' => $line, 'field' => 'join_date', 'message' => 'Format tanggal harus Y-m-d atau d/m/Y.'];
            }
        }
        fclose($handle);

        $existingNips = Employee::query()
            ->where('company_id', $companyId)
            ->whereIn('nip', array_keys($seenNips))
            ->withTrashed()
            ->pluck('deleted_at', 'nip')
            ->all();

        foreach ($existingNips as $nip => $deletedAt) {
            // Hanya blokir kalau karyawan masih aktif (belum soft-delete)
            if ($deletedAt === null) {
                $line = collect($rows)->firstWhere('data.nip', $nip)['line'] ?? 0;
                $errors[] = ['row' => $line, 'field' => 'nip', 'message' => 'NIP sudah terdaftar di perusahaan ini.'];
            }
        }

        if ($errors !== []) {
            return new ImportResult(errors: $errors);
        }

        // Kumpulkan NIP yang perlu di-restore (soft-deleted)
        $deletedNips = array_keys(array_filter($existingNips, fn ($d) => $d !== null));

        DB::transaction(function () use ($rows, $companyId, $deletedNips): void {
            foreach ($rows as $item) {
                $data = $item['data'];
                $date = $this->parseDate($data['join_date']);
                $attributes = [
                    'company_id'          => $companyId,
                    'name'                => $data['name'],
                    'department'          => $data['department'],
                    'position'            => $data['position'],
                    'work_status'         => $data['work_status'],
                    'join_date'           => $date,
                    'basic_salary'        => $data['basic_salary'],
                    'bank_name'           => $data['bank_name'] ?: null,
                    'bank_account_number' => $data['bank_account_number'] ?: null,
                    'bank_account_status' => ($data['bank_name'] || $data['bank_account_number']) ? 'unverified' : null,
                ];

                if (in_array($data['nip'], $deletedNips, true)) {
                    // Restore karyawan yang pernah dihapus dan update datanya
                    Employee::withTrashed()
                        ->where('company_id', $companyId)
                        ->where('nip', $data['nip'])
                        ->restore();

                    Employee::where('company_id', $companyId)
                        ->where('nip', $data['nip'])
                        ->update($attributes);
                } else {
                    Employee::create(array_merge(['nip' => $data['nip']], $attributes));
                }
            }
        });

        return new ImportResult(imported: count($rows));
    }

    private function parseDate(string $value): ?Carbon
    {
        foreach (['Y-m-d', 'd/m/Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date !== false && $date->format($format) === $value) {
                    return $date;
                }
            } catch (\Throwable) {
                // Try the next accepted format.
            }
        }

        return null;
    }
}
