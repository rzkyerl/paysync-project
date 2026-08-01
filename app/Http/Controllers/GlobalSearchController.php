<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    /**
     * Global search: returns matching nav modules and employees as JSON.
     * Scoped to the authenticated user's role and company.
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['modules' => [], 'employees' => []]);
        }

        $user  = $request->user();
        $role  = $user->role ?? '';
        $lower = mb_strtolower($q);

        // ── Module catalogue ────────────────────────────────────────────────
        $allModules = [
            ['slug' => 'dashboard-hr',       'label' => 'Dashboard HR',            'roles' => ['super_admin', 'hr_manager']],
            ['slug' => 'dashboard-finance',  'label' => 'Dashboard Finance',       'roles' => ['super_admin', 'finance_manager']],
            ['slug' => 'dashboard-employee', 'label' => 'Dashboard Employee',      'roles' => ['super_admin', 'employee']],
            ['slug' => 'employees',          'label' => 'Daftar Karyawan',         'roles' => ['super_admin', 'hr_manager', 'finance_manager']],
            ['slug' => 'attendance',         'label' => 'Kehadiran',               'roles' => ['super_admin', 'hr_manager', 'employee']],
            ['slug' => 'payroll',            'label' => 'Proses Payroll',          'roles' => ['super_admin', 'hr_manager']],
            ['slug' => 'approval',           'label' => 'Approval Queue',          'roles' => ['super_admin', 'finance_manager']],
            ['slug' => 'payslips',           'label' => 'Slip Gaji Digital',       'roles' => ['super_admin', 'hr_manager', 'finance_manager', 'employee']],
            ['slug' => 'disbursement',       'label' => 'Batch Transfer',          'roles' => ['super_admin', 'finance_manager']],
            ['slug' => 'reconciliation',     'label' => 'Rekonsiliasi',            'roles' => ['super_admin', 'finance_manager']],
            ['slug' => 'reports',            'label' => 'Reports Hub',             'roles' => ['super_admin', 'hr_manager', 'finance_manager']],
            ['slug' => 'team',               'label' => 'Manajemen Tim',           'roles' => ['super_admin', 'hr_manager']],
            ['slug' => 'settings',           'label' => 'Pengaturan Perusahaan',   'roles' => ['super_admin']],
            ['slug' => 'audit',              'label' => 'Audit Log',               'roles' => ['super_admin']],
        ];

        $moduleUrls = [
            'employees' => route('employees.index'),
            'payroll'   => route('payroll.index'),
        ];

        $modules = [];
        foreach ($allModules as $mod) {
            if (! in_array($role, $mod['roles'], true)) {
                continue;
            }
            if (str_contains(mb_strtolower($mod['label']), $lower) ||
                str_contains(mb_strtolower($mod['slug']), $lower)) {
                $modules[] = [
                    'label' => $mod['label'],
                    'url'   => $moduleUrls[$mod['slug']] ?? url('/app/' . $mod['slug']),
                ];
            }
        }

        // ── Employee search (skip for employee role) ────────────────────────
        $employees = [];
        if (in_array($role, ['super_admin', 'hr_manager', 'finance_manager'], true)) {
            $companyId = $user->company_id;

            $rows = Employee::query()
                ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                ->when(! $companyId, fn ($q) => $q->whereNull('company_id'))
                ->where(fn ($q) => $q
                    ->where('name', 'like', "%{$q}%")
                    ->orWhere('nip',  'like', "%{$q}%")
                )
                ->limit(6)
                ->get(['id', 'name', 'nip', 'department', 'work_status']);

            foreach ($rows as $emp) {
                $employees[] = [
                    'name'       => $emp->name,
                    'nip'        => $emp->nip,
                    'department' => $emp->department ?? '',
                    'url'        => route('employees.show', $emp->id),
                ];
            }
        }

        return response()->json([
            'modules'   => array_slice($modules, 0, 5),
            'employees' => $employees,
        ]);
    }
}
