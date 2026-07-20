<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public const ALLOWED_PAGES = [
        'dashboard-hr',
        'dashboard-finance',
        'dashboard-employee',
        'employees',
        'attendance',
        'payroll',
        'approval',
        'payslips',
        'disbursement',
        'reconciliation',
        'reports',
        'settings',
        'audit',
    ];

    /**
     * Render an application page using the authenticated user's workspace.
     */
    public function show(Request $request, string $page = 'dashboard-hr'): View|RedirectResponse
    {
        abort_unless(in_array($page, self::ALLOWED_PAGES, true), 404);

        $user = $request->user();

        $dashboardRoleMap = [
            'dashboard-hr' => ['super_admin', 'hr_manager'],
            'dashboard-finance' => ['super_admin', 'finance_manager'],
            'dashboard-employee' => ['super_admin', 'employee'],
        ];

        if (isset($dashboardRoleMap[$page]) && ! $user->hasAnyRole($dashboardRoleMap[$page])) {
            return redirect()->to('/app/'.$user->defaultDashboard());
        }

        $isDemoUser = $user->isDemoUser();

        if ($isDemoUser && ! $user->company_id) {
            Log::warning("Demo user {$user->email} memiliki company_id null");
        }

        $workspaceData = $isDemoUser && $user->company_id
            ? $this->loadDemoData($user->company_id, $page)
            : $this->loadUserData($user->company_id, $page);

        $sharedData = [
            'isDemoUser'          => $isDemoUser,
            'isEmpty'             => $workspaceData['isEmpty'],
            'companyName'         => $user->company?->name ?? 'Workspace',
            'isSuperAdminViewing' => $user->isSuperAdmin(),
        ];

        // Preserve the existing, richer role dashboards while Phase 4 updates
        // their views to consume the new company-scoped data directly.
        if ($page === 'dashboard-hr') {
            return $this->hr($request)->with($sharedData);
        }

        if ($page === 'dashboard-finance') {
            return $this->finance($request)->with($sharedData);
        }

        if ($page === 'dashboard-employee') {
            return $this->employee($request)->with($sharedData);
        }

        // Inject payslips-specific data
        if ($page === 'payslips' && $user->company_id) {
            $selectedPeriod = $request->input('period');
            $publishedPayrolls = Payroll::where('company_id', $user->company_id)
                ->whereIn('status', ['approved', 'disbursed'])
                ->orderByDesc('period')
                ->get();

            $activePayroll = $selectedPeriod
                ? $publishedPayrolls->firstWhere('period', $selectedPeriod)
                : $publishedPayrolls->first();

            $payslipItems = $activePayroll
                ? $activePayroll->payrollItems()->with('employee')->get()
                : collect();

            $workspaceData = array_merge($workspaceData, [
                'publishedPayrolls' => $publishedPayrolls,
                'activePayroll'     => $activePayroll,
                'payslipItems'      => $payslipItems,
            ]);
        }

        // Inject disbursement-specific data
        if ($page === 'disbursement' && $user->company_id) {
            $disbursementPayrolls = Payroll::where('company_id', $user->company_id)
                ->whereIn('status', ['approved', 'disbursed'])
                ->with(['approver', 'payrollItems'])
                ->orderByDesc('period')
                ->get();

            $readyCount      = $disbursementPayrolls->where('status', 'approved')->count();
            $disbursedCount  = $disbursementPayrolls->where('status', 'disbursed')->count();
            $successAmount   = $disbursementPayrolls->where('status', 'disbursed')->sum('net_total');
            $pendingAmount   = $disbursementPayrolls->where('status', 'approved')->sum('net_total');

            $workspaceData = array_merge($workspaceData, [
                'disbursementPayrolls' => $disbursementPayrolls,
                'disbReadyCount'       => $readyCount,
                'disbDisbursedCount'   => $disbursedCount,
                'disbSuccessAmount'    => $successAmount,
                'disbPendingAmount'    => $pendingAmount,
            ]);
        }

        // Inject reconciliation-specific data
        if ($page === 'reconciliation' && $user->company_id) {
            $reconPayrolls = Payroll::where('company_id', $user->company_id)
                ->whereIn('status', ['approved', 'disbursed'])
                ->with(['approver', 'payrollItems.employee'])
                ->orderByDesc('period')
                ->get();

            $totalNetPay       = $reconPayrolls->sum('net_total');
            $transferredTotal  = $reconPayrolls->sum(fn ($p) =>
                $p->payrollItems->where('status', 'transferred')->sum('net_pay')
            );
            $difference        = (float)$totalNetPay - (float)$transferredTotal;

            $workspaceData = array_merge($workspaceData, [
                'reconPayrolls'   => $reconPayrolls,
                'reconTotalNet'   => $totalNetPay,
                'reconTransferred'=> $transferredTotal,
                'reconDifference' => $difference,
            ]);
        }

        // Inject reports-specific data
        if ($page === 'reports' && $user->company_id) {
            $reportPayrolls = Payroll::where('company_id', $user->company_id)
                ->with(['payrollItems.employee', 'submitter', 'approver'])
                ->orderByDesc('period')
                ->get();

            $latestPayroll     = $reportPayrolls->first();
            $reportPayrollItems = $latestPayroll
                ? $latestPayroll->payrollItems()->with('employee')->get()
                : collect();

            $workspaceData = array_merge($workspaceData, [
                'reportPayrolls'     => $reportPayrolls,
                'reportLatestPayroll'=> $latestPayroll,
                'reportPayrollItems' => $reportPayrollItems,
            ]);
        }

        // Inject audit-specific data
        if ($page === 'audit' && $user->company_id) {
            $auditLogs = \App\Models\SettingsAuditLog::where('company_id', $user->company_id)
                ->with('user')
                ->orderByDesc('changed_at')
                ->paginate(30);

            $workspaceData = array_merge($workspaceData, [
                'auditLogs' => $auditLogs,
            ]);
        }

        // Inject attendance-specific data
        if ($page === 'attendance' && $user->company_id) {
            $attendancePayrolls = Payroll::where('company_id', $user->company_id)
                ->orderByDesc('period')
                ->get(['id', 'period', 'period_label', 'status']);

            $selectedPayrollId  = request('payroll_id');
            $attendancePayroll  = $selectedPayrollId
                ? $attendancePayrolls->firstWhere('id', $selectedPayrollId)
                : $attendancePayrolls->first();

            $attendanceRecords  = $attendancePayroll
                ? \App\Models\AttendanceRecord::where('payroll_id', $attendancePayroll->id)
                    ->with('employee')
                    ->get()
                : collect();

            $workspaceData = array_merge($workspaceData, [
                'attendancePayrolls' => $attendancePayrolls,
                'attendancePayroll'  => $attendancePayroll,
                'attendanceRecords'  => $attendanceRecords,
            ]);
        }

        return view('payflow.app', array_merge($workspaceData, $sharedData, [
            'page' => $page,
        ]));
    }

    /**
     * Load data scoped exclusively to a demo company.
     *
     * @return array<string, mixed>
     */
    private function loadDemoData(int $companyId, string $page): array
    {
        $company = Company::with(['employees', 'payrolls'])->findOrFail($companyId);

        return [
            'employees' => $company->employees()->latest()->limit(5)->get(),
            'payroll' => $company->payrolls()->where('status', 'needs_review')->latest()->first(),
            'approvalQueue' => $company->payrolls()
                ->with('submitter')
                ->where('status', 'pending_approval')
                ->latest()
                ->get(),
            'kpis' => $this->buildKpis($company, $page),
            'isEmpty' => false,
        ];
    }

    /**
     * Load an empty slate or data scoped to a real user's company.
     *
     * @return array<string, mixed>
     */
    private function loadUserData(?int $companyId, string $page): array
    {
        if (! $companyId) {
            return [
                'employees' => collect(),
                'payroll' => null,
                'approvalQueue' => collect(),
                'kpis' => [],
                'isEmpty' => true,
            ];
        }

        $company = Company::with(['employees', 'payrolls'])->findOrFail($companyId);
        $employees = $company->employees()->latest()->limit(5)->get();

        return [
            'employees' => $employees,
            'payroll' => $company->payrolls()->latest()->first(),
            'approvalQueue' => $company->payrolls()
                ->with('submitter')
                ->where('status', 'pending_approval')
                ->latest()
                ->get(),
            'kpis' => $this->buildKpis($company, $page),
            'isEmpty' => $employees->isEmpty() && $company->payrolls->isEmpty(),
        ];
    }

    /**
     * Build company-scoped KPI data for transitional and Phase 4 views.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildKpis(Company $company, string $page): array
    {
        $activeEmployees = $company->employees()
            ->whereIn('work_status', ['active', 'probation', 'contract'])
            ->count();
        $payroll = $company->payrolls()->latest()->first();

        return [
            ['label' => 'Karyawan Aktif', 'value' => $activeEmployees, 'sub' => $company->name],
            ['label' => 'Status Payroll', 'value' => $payroll?->status ?? 'Belum ada', 'sub' => $payroll?->period_label ?? '-'],
            ['label' => 'Total Gross', 'value' => $payroll ? 'Rp '.number_format((float) $payroll->gross_total, 0, ',', '.') : '—', 'sub' => $page],
            ['label' => 'Total Net', 'value' => $payroll ? 'Rp '.number_format((float) $payroll->net_total, 0, ',', '.') : '—', 'sub' => $page],
        ];
    }

    /**
     * Dashboard for HR Manager role.
     * Requires: authenticated user with role 'hr_manager'.
     */
    public function hr(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        if (! $user->isDemoUser() && $user->company_id !== null && ! $user->hasAnyRole(['super_admin', 'hr_manager'])) {
            return redirect()->to('/app/'.$user->defaultDashboard());
        }

        $error = null;

        try {
            $user = $request->user();
            $companyId = $user->company_id;

            // ----------------------------------------------------------------
            // 3.1 KPI Cards
            // ----------------------------------------------------------------

            // Total active employees (active + probation + contract, not inactive)
            // Scoped to the user's company; if no company yet, returns 0.
            $employeeQuery = fn () => Employee::query()
                ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                ->when(! $companyId, fn ($q) => $q->whereNull('company_id'));

            $activeEmployeeCount = $employeeQuery()->whereIn('work_status', ['active', 'probation', 'contract'])->count();
            $probationCount = $employeeQuery()->where('work_status', 'probation')->count();

            // Most recent non-draft payroll scoped to the user's company
            $payrollQuery = fn () => Payroll::query()
                ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                ->when(! $companyId, fn ($q) => $q->whereNull('company_id'));

            $activePayroll = $payrollQuery()->whereNotIn('status', ['draft'])
                ->latest('updated_at')
                ->first();

            // Fallback to latest draft if nothing else
            if (! $activePayroll) {
                $activePayroll = $payrollQuery()->latest('updated_at')->first();
            }

            // Payroll status label map (Indonesian)
            $statusLabel = [
                'draft' => 'Draft',
                'needs_review' => 'Perlu Review',
                'pending_approval' => 'Menunggu Approval',
                'approved' => 'Disetujui',
                'disbursed' => 'Transfer Selesai',
            ];

            $payrollStatusText = $activePayroll
                ? ($statusLabel[$activePayroll->status] ?? $activePayroll->status)
                : 'Tidak Ada';

            $payrollAnomalyCount = $activePayroll ? $activePayroll->anomaly_count : 0;

            // Estimated net pay: sum of all active payroll periods' net_total
            // We use the active payroll's net_total as the estimate
            $estimatedNetPay = $activePayroll ? (float) $activePayroll->net_total : 0.0;
            $estimatedNetPayFormatted = 'Rp '.$this->formatRupiah($estimatedNetPay);

            // Attendance % — no attendance table exists; derive from anomaly data
            // We represent attendance as "no missing data" indicator based on payroll anomalies
            // Since there is no attendance table, we show N/A or compute from payroll anomaly_count
            $attendancePercent = 'N/A';
            $attendanceSubLabel = 'Belum ada data kehadiran';

            $kpis = [
                [
                    'label' => 'Karyawan Aktif',
                    'value' => $activeEmployeeCount,
                    'sub' => $probationCount.' probation',
                    'badge' => 'badge-blue',
                ],
                [
                    'label' => 'Kehadiran Periode Ini',
                    'value' => $attendancePercent,
                    'sub' => $attendanceSubLabel,
                    'badge' => 'badge-amber',
                ],
                [
                    'label' => 'Status Payroll',
                    'value' => $payrollStatusText,
                    'sub' => $payrollAnomalyCount.' anomali',
                    'badge' => $activePayroll && $activePayroll->status === 'needs_review' ? 'badge-amber' : 'badge-blue',
                ],
                [
                    'label' => 'Estimasi Take-home Pay',
                    'value' => $estimatedNetPayFormatted,
                    'sub' => $activePayroll ? 'Periode '.$activePayroll->period_label : '-',
                    'badge' => 'badge-green',
                ],
            ];

            // ----------------------------------------------------------------
            // 3.2 Payroll Timeline
            // ----------------------------------------------------------------
            // Stages derived from $activePayroll->status
            $payrollStatus = $activePayroll ? $activePayroll->status : 'draft';

            $stages = [
                'Import Kehadiran',
                'Kalkulasi',
                'Review Anomali',
                'Approval',
                'Slip Terbit',
                'Transfer',
            ];

            // Determine which stage is "active" based on the payroll status enum
            $stageStatusMap = [
                // [completed_if_status_in, active_if_status]
                'Import Kehadiran' => [
                    'done' => ! in_array($payrollStatus, ['draft']),
                    'active' => $payrollStatus === 'draft',
                ],
                'Kalkulasi' => [
                    'done' => in_array($payrollStatus, ['needs_review', 'pending_approval', 'approved', 'disbursed']),
                    'active' => false,
                ],
                'Review Anomali' => [
                    'done' => in_array($payrollStatus, ['pending_approval', 'approved', 'disbursed']),
                    'active' => $payrollStatus === 'needs_review',
                ],
                'Approval' => [
                    'done' => in_array($payrollStatus, ['approved', 'disbursed']),
                    'active' => $payrollStatus === 'pending_approval',
                ],
                'Slip Terbit' => [
                    'done' => $payrollStatus === 'disbursed',
                    'active' => $payrollStatus === 'approved',
                ],
                'Transfer' => [
                    'done' => $payrollStatus === 'disbursed',
                    'active' => false,
                ],
            ];

            $payrollTimeline = [];
            foreach ($stages as $idx => $stage) {
                $stageInfo = $stageStatusMap[$stage];
                if ($stageInfo['done']) {
                    $status = 'done';
                    $statusText = 'Selesai';
                    $badgeClass = 'badge-green';
                    $dotClass = 'done';
                } elseif ($stageInfo['active']) {
                    $status = 'active';
                    $statusText = 'Aktif';
                    $badgeClass = 'badge-amber';
                    $dotClass = 'warn';
                } else {
                    $status = 'waiting';
                    $statusText = 'Menunggu';
                    $badgeClass = '';
                    $dotClass = '';
                }

                $payrollTimeline[] = [
                    'step' => $idx + 1,
                    'label' => $stage,
                    'status' => $status,
                    'statusText' => $statusText,
                    'badgeClass' => $badgeClass,
                    'dotClass' => $dotClass,
                ];
            }

            // Find the active stage label for the timeline header badge
            $activeStageLabel = 'Draft';
            foreach ($payrollTimeline as $t) {
                if ($t['status'] === 'active') {
                    $activeStageLabel = $t['label'].' aktif';
                    break;
                }
            }

            // ----------------------------------------------------------------
            // 3.3 Action Center
            // ----------------------------------------------------------------

            // Rekening belum terverifikasi — scoped to company
            $unverifiedBankCount = $employeeQuery()->where('bank_account_status', 'unverified')->count();

            // Rekening rejected — scoped to company
            $rejectedBankCount = $employeeQuery()->where('bank_account_status', 'rejected')->count();

            // Anomali payroll (from active payroll)
            $payrollAnomalies = $activePayroll ? $activePayroll->anomaly_count : 0;

            // Payroll pending approval — scoped to company
            $pendingApprovalCount = $payrollQuery()->where('status', 'pending_approval')->count();

            // Kehadiran belum lengkap — no attendance table exists, show 0
            $incompleteAttendanceCount = 0;

            $actionItems = [];

            if ($unverifiedBankCount > 0) {
                $actionItems[] = [
                    'text' => "Rekening belum terverifikasi: {$unverifiedBankCount} karyawan",
                    'level' => 'warning',
                ];
            }

            if ($rejectedBankCount > 0) {
                $actionItems[] = [
                    'text' => "Rekening ditolak: {$rejectedBankCount} karyawan",
                    'level' => 'danger',
                ];
            }

            if ($incompleteAttendanceCount > 0) {
                $actionItems[] = [
                    'text' => "Data kehadiran belum lengkap: {$incompleteAttendanceCount} baris",
                    'level' => 'warning',
                ];
            }

            if ($payrollAnomalies > 0) {
                $actionItems[] = [
                    'text' => "Payroll memiliki anomali: {$payrollAnomalies} kritis",
                    'level' => 'danger',
                ];
            }

            if ($pendingApprovalCount > 0) {
                $actionItems[] = [
                    'text' => "Payroll menunggu approval Finance: {$pendingApprovalCount} periode",
                    'level' => 'info',
                ];
            }

            $actionItemCount = count($actionItems);

            // ----------------------------------------------------------------
            // 3.4 Recent Employee Changes — 5 most recently modified, scoped to company
            // ----------------------------------------------------------------
            $recentEmployees = $employeeQuery()->orderByDesc('updated_at')
                ->limit(5)
                ->get();

        } catch (\Throwable $e) {
            Log::error('DashboardController::hr() error: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            $error = 'Terjadi kesalahan saat memuat data dashboard. Silakan coba lagi.';
            $kpis = [];
            $payrollTimeline = [];
            $activeStageLabel = 'Error';
            $actionItems = [];
            $actionItemCount = 0;
            $recentEmployees = collect();
        }

        $data = [
            'isSuperAdminViewing' => $user->isSuperAdmin(),
            'kpis' => $kpis,
            'payrollTimeline' => $payrollTimeline,
            'activeStageLabel' => $activeStageLabel,
            'actionItems' => $actionItems,
            'actionItemCount' => $actionItemCount,
            'recentEmployees' => $recentEmployees,
            'error' => $error,
        ];

        return view('payflow.app', array_merge(['page' => 'dashboard-hr'], $data));
    }

    /**
     * Format a number as compact Rupiah string (e.g. 102150000 → "102,15 Jt").
     */
    private function formatRupiah(float $amount): string
    {
        if ($amount >= 1_000_000_000) {
            return number_format($amount / 1_000_000_000, 2, ',', '.').' M';
        }
        if ($amount >= 1_000_000) {
            return number_format($amount / 1_000_000, 2, ',', '.').' Jt';
        }

        return number_format($amount, 0, ',', '.');
    }

    /**
     * Dashboard for Finance Manager role.
     * Requires: authenticated user with role 'finance_manager'.
     */
    public function finance(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        if (! $user->isDemoUser() && $user->company_id !== null && ! $user->hasAnyRole(['super_admin', 'finance_manager'])) {
            return redirect()->to('/app/'.$user->defaultDashboard());
        }

        $error = null;

        try {
            $user = $request->user();
            $companyId = $user->company_id;

            // Reusable scoped query builder for Payroll and Employee
            $payrollQuery = fn () => Payroll::query()
                ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                ->when(! $companyId, fn ($q) => $q->whereNull('company_id'));

            $employeeQuery = fn () => Employee::query()
                ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                ->when(! $companyId, fn ($q) => $q->whereNull('company_id'));

            // ----------------------------------------------------------------
            // 4.1 KPI Cards
            // ----------------------------------------------------------------

            // Count of payrolls waiting for approval — scoped to company
            $pendingApprovalCount = $payrollQuery()->where('status', 'pending_approval')->count();

            // Total net_total for approved or disbursed payrolls — scoped to company
            $totalNominalApproved = (float) $payrollQuery()->whereIn('status', ['approved', 'disbursed'])
                ->sum('net_total');

            // Transfer failed = 0 (no failed disbursement data in current schema)
            $transferFailedCount = 0;

            // Employee count from the most recent disbursed payroll — scoped to company
            $disbursedPayroll = $payrollQuery()->where('status', 'disbursed')
                ->latest('updated_at')
                ->first();

            $kpis = [
                [
                    'label' => 'Payroll Menunggu Persetujuan',
                    'value' => $pendingApprovalCount,
                    'sub' => $pendingApprovalCount > 0 ? 'Prioritas tinggi' : 'Semua beres',
                    'badge' => $pendingApprovalCount > 0 ? 'badge-amber' : 'badge-green',
                ],
                [
                    'label' => 'Total Nominal Disetujui',
                    'value' => 'Rp '.$this->formatRupiah($totalNominalApproved),
                    'sub' => $payrollQuery()->whereIn('status', ['approved', 'disbursed'])->count().' periode',
                    'badge' => 'badge-blue',
                ],
                [
                    'label' => 'Transfer Berhasil',
                    'value' => $disbursedPayroll ? number_format($disbursedPayroll->employee_count, 0, ',', '.') : '0',
                    'sub' => $disbursedPayroll ? 'Periode '.$disbursedPayroll->period_label : '-',
                    'badge' => 'badge-green',
                ],
                [
                    'label' => 'Transfer Gagal',
                    'value' => $transferFailedCount,
                    'sub' => $transferFailedCount > 0 ? 'Perlu retry' : 'Tidak ada',
                    'badge' => $transferFailedCount > 0 ? 'badge-red' : 'badge-green',
                ],
            ];

            // ----------------------------------------------------------------
            // 4.2 Transfer Batch Status — percentages, scoped to company
            // ----------------------------------------------------------------
            $totalPayrolls = $payrollQuery()->count();

            if ($totalPayrolls > 0) {
                $successCount = $payrollQuery()->where('status', 'disbursed')->count();
                $processingCount = $payrollQuery()->where('status', 'approved')->count();
                $failedCount = 0; // no failed status in current schema

                $successPct = (int) round(($successCount / $totalPayrolls) * 100);
                $processingPct = (int) round(($processingCount / $totalPayrolls) * 100);
                $failedPct = (int) round(($failedCount / $totalPayrolls) * 100);
            } else {
                $successPct = 0;
                $processingPct = 0;
                $failedPct = 0;
            }

            $transferBatchStatus = [
                ['label' => 'Success',    'percent' => $successPct,    'badge' => 'badge-green'],
                ['label' => 'Processing', 'percent' => $processingPct, 'badge' => 'badge-blue'],
                ['label' => 'Failed',     'percent' => $failedPct,     'badge' => 'badge-red'],
            ];

            // ----------------------------------------------------------------
            // 4.3 Rekonsiliasi Summary — scoped to company
            // ----------------------------------------------------------------
            $matchedTotal = (float) $payrollQuery()->where('status', 'disbursed')->sum('net_total');

            $totalAnomalies = $payrollQuery()->whereNotIn('status', ['draft'])->sum('anomaly_count');

            // Estimate mismatch amount: anomalies × avg basic_salary of active employees in company
            $avgSalary = (float) $employeeQuery()
                ->whereIn('work_status', ['active', 'probation', 'contract'])
                ->avg('basic_salary') ?: 0.0;

            $mismatchTotal = $totalAnomalies > 0 ? (float) ($totalAnomalies * $avgSalary) : 0.0;

            $reconciliation = [
                'matched' => $matchedTotal,
                'mismatch' => $mismatchTotal,
                'matched_label' => 'Rp '.$this->formatRupiah($matchedTotal),
                'mismatch_label' => $mismatchTotal > 0
                    ? 'Rp '.$this->formatRupiah($mismatchTotal)
                    : 'Tidak ada selisih',
                'has_mismatch' => $mismatchTotal > 0,
            ];

            // ----------------------------------------------------------------
            // 4.2 (also) Approval Queue — payrolls pending approval, scoped to company
            // ----------------------------------------------------------------
            $approvalQueue = $payrollQuery()->where('status', 'pending_approval')
                ->with('submitter')
                ->orderByDesc('updated_at')
                ->get();

        } catch (\Throwable $e) {
            Log::error('DashboardController::finance() error: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            $error = 'Terjadi kesalahan saat memuat data dashboard. Silakan coba lagi.';
            $kpis = [];
            $approvalQueue = collect();
            $transferBatchStatus = [];
            $reconciliation = [
                'matched_label' => 'N/A',
                'mismatch_label' => 'N/A',
                'has_mismatch' => false,
            ];
        }

        $data = [
            'isSuperAdminViewing' => $user->isSuperAdmin(),
            'kpis' => $kpis,
            'approvalQueue' => $approvalQueue,
            'transferBatchStatus' => $transferBatchStatus,
            'reconciliation' => $reconciliation,
            'error' => $error,
        ];

        return view('payflow.app', array_merge(['page' => 'dashboard-finance'], $data));
    }

    /**
     * Dashboard for Employee role.
     * Requires: authenticated user with role 'employee'.
     *
     * Satisfies Requirements 3.1–3.5.
     */
    public function employee(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        if (! $user->isDemoUser() && $user->company_id !== null && ! $user->hasAnyRole(['super_admin', 'employee'])) {
            return redirect()->to('/app/'.$user->defaultDashboard());
        }

        $error = null;
        $kpis = [];
        $lastPayslip = null;
        $employee = null;

        try {
            // ----------------------------------------------------------------
            // 5.3 Bank Account Status — load the Employee linked to this user
            // ----------------------------------------------------------------
            $employeeId = $request->user()->employee_id;

            if ($employeeId) {
                $employee = Employee::find($employeeId);

                // Policy check: employee may only view their own record (Req 3.4)
                if ($employee) {
                    Gate::authorize('view', $employee);
                }
            }

            // ----------------------------------------------------------------
            // 5.2 Last Payslip — most recent disbursed or approved payroll
            // ----------------------------------------------------------------
            $lastPayslip = Payroll::whereIn('status', ['disbursed', 'approved'])
                ->latest('updated_at')
                ->first();

            // ----------------------------------------------------------------
            // 5.1 KPI Calculations
            // ----------------------------------------------------------------

            // --- Next Payday ---
            // Find the latest payroll that has been disbursed or approved.
            // The "next payday" = last day of that payroll period's month.
            $referencePayroll = $lastPayslip;

            $nextPaydayDate = null;
            $nextPaydayDays = null;
            $nextPaydayLabel = '-';

            if ($referencePayroll) {
                // period format: "YYYY-MM"
                try {
                    $periodDate = Carbon::createFromFormat('Y-m', $referencePayroll->period);
                    $nextPaydayDate = $periodDate->copy()->endOfMonth()->startOfDay();
                    $now = Carbon::now()->startOfDay();

                    // Positive = payday is in the future, negative = already passed
                    $diffDays = (int) $now->diffInDays($nextPaydayDate, false);

                    $nextPaydayLabel = $nextPaydayDate->translatedFormat('d M Y');
                    $nextPaydayDays = $diffDays;
                } catch (\Exception $e) {
                    // Leave as null if period is malformed
                }
            }

            // --- Kehadiran Bulan Ini (Attendance this month) ---
            // No attendance table exists. Estimate using weekdays elapsed in the current month.
            $today = Carbon::now();
            $startOfMonth = $today->copy()->startOfMonth();
            $totalWeekdays = 0;
            $elapsedWeekdays = 0;

            $cursor = $startOfMonth->copy();
            $endOfMonth = $today->copy()->endOfMonth();

            while ($cursor->lte($endOfMonth)) {
                if (! $cursor->isWeekend()) {
                    $totalWeekdays++;
                    if ($cursor->lte($today)) {
                        $elapsedWeekdays++;
                    }
                }
                $cursor->addDay();
            }

            $attendanceThisMonth = $elapsedWeekdays;
            $attendanceTotalDays = $totalWeekdays;

            // --- Lembur Bulan Ini (Overtime) ---
            // No overtime table — return 0 as default per spec guidance.
            $overtimeHours = 0;

            // --- Last Take-home Pay ---
            $lastTakeHome = $lastPayslip ? (float) $lastPayslip->net_total : 0.0;
            $lastTakeHomeFormatted = $lastPayslip
                ? 'Rp '.$this->formatRupiah($lastTakeHome)
                : '-';

            // Build KPI array (Req 3.1)
            $kpis = [
                'next_payday_date' => $nextPaydayLabel,
                'next_payday_days' => $nextPaydayDays,
                'attendance_this_month' => $attendanceThisMonth,
                'attendance_total_days' => $attendanceTotalDays,
                'overtime_hours' => $overtimeHours,
                'last_take_home' => $lastTakeHomeFormatted,
            ];

        } catch (\Throwable $e) {
            Log::error('DashboardController::employee() error: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            $error = 'Terjadi kesalahan saat memuat data dashboard. Silakan coba lagi.';
            $kpis = [
                'next_payday_date' => '-',
                'next_payday_days' => null,
                'attendance_this_month' => 0,
                'attendance_total_days' => 0,
                'overtime_hours' => 0,
                'last_take_home' => '-',
            ];
            $lastPayslip = null;
            $employee = null;
        }

        $data = [
            'isSuperAdminViewing' => $user->isSuperAdmin(),
            'kpis' => $kpis,
            'lastPayslip' => $lastPayslip,
            'employee' => $employee,
            'error' => $error,
        ];

        return view('payflow.app', array_merge(['page' => 'dashboard-employee'], $data));
    }
}
