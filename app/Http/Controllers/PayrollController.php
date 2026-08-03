<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Services\ActivityLogger;
use App\Services\AttendanceCsvImporter;
use App\Services\NotificationService;
use App\Services\PayrollService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollController extends Controller
{
    private const ALLOWED_STATUSES = ['draft', 'needs_review', 'pending_approval', 'approved', 'disbursed', 'disputed'];

    private const ALLOWED_SORT_COLUMNS = ['period', 'employee_count', 'gross_total', 'net_total', 'status'];

    private const ALLOWED_PER_PAGE = [15, 30, 50];

    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        abort_if(! $user->hasAnyRole(['super_admin', 'hr_manager', 'finance_manager']) || $user->company_id === null, 403);
        $statusFilter = in_array($request->status, self::ALLOWED_STATUSES, true) ? $request->status : null;
        $sortBy = in_array($request->sort, self::ALLOWED_SORT_COLUMNS, true) ? $request->sort : 'period';
        $sortDir = in_array($request->dir, ['asc', 'desc'], true) ? $request->dir : 'desc';
        $perPage = in_array((int) $request->per_page, self::ALLOWED_PER_PAGE, true) ? (int) $request->per_page : 15;
        $payrolls = Payroll::query()
            ->where('company_id', $user->company_id)
            ->when($request->period, fn ($q, $period) => $q->where('period', 'like', "%{$period}%"))
            ->when($statusFilter, fn ($q, $status) => $q->where('status', $status))
            ->orderBy($sortBy, $sortDir)->paginate($perPage)->withQueryString();

        return view('payflow.app', [
            'page' => 'payroll', 'payrolls' => $payrolls, 'statusFilter' => $statusFilter,
            'periodFilter' => $request->period ?? '', 'allowedStatuses' => self::ALLOWED_STATUSES,
            'sortBy' => $sortBy, 'sortDir' => $sortDir, 'perPage' => $perPage,
            'isDemoUser' => $user->isDemoUser(), 'isEmpty' => $payrolls->total() === 0,
            'companyName' => $user->company?->name ?? 'Workspace',
        ]);
    }

    public function create(Request $request): View
    {
        abort_if(! $request->user()->isHrManager(), 403);

        return view('payflow.payroll.create');
    }

    public function storeNew(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if(! $user->isHrManager(), 403);
        $validated = $request->validate([
            'period' => ['required', 'date_format:Y-m'],
            'period_label' => ['required', 'string', 'max:100'],
        ]);

        if (Payroll::where('company_id', $user->company_id)->where('period', $validated['period'])->exists()) {
            return back()->withInput()->withErrors(['period' => 'Periode payroll tersebut sudah ada.']);
        }

        DB::transaction(function () use ($user, $validated) {
            // Use firstOrCreate to atomically handle the race condition:
            // even if the pre-check passes, a concurrent request could
            // insert a duplicate — firstOrCreate + the DB unique constraint
            // will ensure only one row is created.
            Payroll::firstOrCreate(
                [
                    'company_id' => $user->company_id,
                    'period' => $validated['period'],
                ],
                [
                    'period_label' => $validated['period_label'],
                    'status' => 'draft',
                    'employee_count' => 0,
                    'gross_total' => 0,
                    'deduction_total' => 0,
                    'net_total' => 0,
                ]
            );
        });

        $payroll = Payroll::where('company_id', $user->company_id)
            ->where('period', $validated['period'])
            ->firstOrFail();

        ActivityLogger::record($request, 'payroll', 'payroll.created',
            "Payroll {$payroll->period_label} dibuat",
            'payroll', $payroll->id, ['period' => $payroll->period]
        );

        return redirect()->route('payroll.show', $payroll)->with('status', 'Payroll draft berhasil dibuat.');
    }

    public function show(Request $request, Payroll $payroll): View
    {
        $this->authorizePayroll($request, $payroll, ['super_admin', 'hr_manager', 'finance_manager']);
        $payroll->load(['payrollItems.employee', 'submitter', 'approver']);

        return view('payflow.payroll.show', [
            'payroll' => $payroll,
            'anomalies' => $payroll->payrollItems->where('has_anomaly', true),
            'canSubmit' => app(PayrollService::class)->canSubmit($payroll),
        ]);
    }

    public function importAttendance(Request $request, Payroll $payroll): View|RedirectResponse
    {
        $this->authorizePayroll($request, $payroll, ['hr_manager']);
        if (! in_array($payroll->status, ['draft', 'needs_review'], true)) {
            session()->flash('toast', [
                'type'    => 'warning',
                'message' => "Payroll {$payroll->period_label} sudah berstatus ".ucfirst(str_replace('_', ' ', $payroll->status))." dan tidak bisa diubah.",
            ]);

            return redirect()->route('app.attendance');
        }

        return view('payflow.payroll.attendance-import', compact('payroll'));
    }

    public function storeAttendance(Request $request, Payroll $payroll, AttendanceCsvImporter $importer): RedirectResponse
    {
        $this->authorizePayroll($request, $payroll, ['hr_manager']);
        abort_if(! in_array($payroll->status, ['draft', 'needs_review'], true), 422);
        $request->validate(['file' => ['required', 'file', 'extensions:csv', 'mimes:csv,txt', 'max:2048']]);
        $result = $importer->import($request->file('file'), $payroll);
        if (! $result->successful()) {
            app(NotificationService::class)->notifyUser(
                $request->user(),
                'attendance.import_failed',
                'Import kehadiran gagal',
                count($result->errors).' masalah ditemukan pada file CSV.',
                route('payroll.attendance.import', $payroll, false),
                ['payroll_id' => $payroll->id, 'error_count' => count($result->errors)],
            );

            return back()->with('import_errors', $result->errors);
        }

        ActivityLogger::record($request, 'attendance', 'attendance.imported',
            "Import {$result->imported} record kehadiran — {$payroll->period_label}",
            'payroll', $payroll->id, ['count' => $result->imported, 'period' => $payroll->period]
        );

        app(NotificationService::class)->notifyUser(
            $request->user(),
            'attendance.imported',
            'Import kehadiran berhasil',
            "{$result->imported} record kehadiran {$payroll->period_label} berhasil diimport.",
            route('payroll.show', $payroll, false),
            ['payroll_id' => $payroll->id, 'count' => $result->imported],
        );

        return redirect()->route('payroll.show', $payroll)->with('status', $result->imported.' record kehadiran berhasil diimport.');
    }

    public function downloadAttendanceTemplate(Request $request)
    {
        abort_if(! $request->user()->hasAnyRole(['super_admin', 'hr_manager', 'finance_manager']), 403);

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, ['nip', 'work_days', 'days_present', 'overtime_hours', 'leave_days']);
            fputcsv($handle, ['EMP-0001', 22, 22, 0, 0]);
            fputcsv($handle, ['EMP-0002', 22, 20, 2, 1]);
            fclose($handle);
        }, 'attendance-template.csv', ['Content-Type' => 'text/csv']);
    }

    public function calculate(Request $request, Payroll $payroll, PayrollService $service): RedirectResponse
    {
        $this->authorizePayroll($request, $payroll, ['hr_manager']);
        abort_if(! in_array($payroll->status, ['draft', 'needs_review'], true), 422);
        $service->calculate($payroll);

        ActivityLogger::record($request, 'payroll', 'payroll.calculated',
            "Payroll {$payroll->period_label} dikalkulasi",
            'payroll', $payroll->id, ['period' => $payroll->period]
        );

        $anomalyCount = $payroll->payrollItems()
            ->where('has_anomaly', true)
            ->where('anomaly_acknowledged', false)
            ->count();

        if ($anomalyCount > 0) {
            app(NotificationService::class)->notifyUser(
                $request->user(),
                'payroll.anomaly_detected',
                'Anomali payroll ditemukan',
                "{$anomalyCount} anomali pada payroll {$payroll->period_label} perlu ditinjau.",
                route('payroll.show', $payroll, false),
                ['payroll_id' => $payroll->id, 'count' => $anomalyCount],
            );
        }

        return redirect()->route('payroll.show', $payroll)->with('status', 'Payroll berhasil dihitung dan masuk Needs Review.');
    }

    public function acknowledgeAnomaly(Request $request, Payroll $payroll, PayrollItem $item, PayrollService $service): RedirectResponse
    {
        $this->authorizePayroll($request, $payroll, ['hr_manager']);
        abort_if($item->payroll_id !== $payroll->id || $item->company_id !== $request->user()->company_id, 404);
        $service->acknowledgeAnomaly($item);

        return back()->with('status', 'Anomali berhasil di-acknowledge.');
    }

    public function submit(Request $request, Payroll $payroll, PayrollService $service): RedirectResponse
    {
        $this->authorizePayroll($request, $payroll, ['hr_manager']);
        abort_if($payroll->status !== 'needs_review', 422);
        if (! $service->canSubmit($payroll)) {
            return back()->with('error', 'Masih ada anomali yang belum di-acknowledge.');
        }

        DB::transaction(function () use ($payroll, $request): void {
            $payroll->transitionTo('pending_approval', ['submitted_by' => $request->user()->id]);
        });

        ActivityLogger::record($request, 'payroll', 'payroll.submitted',
            "Payroll {$payroll->period_label} diajukan untuk persetujuan",
            'payroll', $payroll->id, ['period' => $payroll->period]
        );

        app(NotificationService::class)->notifyRoles(
            $payroll->company_id,
            ['finance_manager', 'super_admin'],
            'payroll.awaiting_approval',
            'Payroll menunggu persetujuan',
            "Payroll {$payroll->period_label} siap ditinjau dan disetujui.",
            route('payroll.show', $payroll, false),
            ['payroll_id' => $payroll->id, 'period' => $payroll->period],
        );

        return back()->with('status', 'Payroll berhasil diajukan untuk persetujuan.');
    }

    public function approve(Request $request, Payroll $payroll): RedirectResponse
    {
        $this->authorizePayroll($request, $payroll, ['finance_manager', 'super_admin']);
        abort_if($payroll->status !== 'pending_approval', 422);

        DB::transaction(function () use ($payroll, $request): void {
            $payroll->transitionTo('approved', ['approved_by' => $request->user()->id]);
        });

        ActivityLogger::record($request, 'payroll', 'payroll.approved',
            "Payroll {$payroll->period_label} disetujui",
            'payroll', $payroll->id,
            ['period' => $payroll->period, 'net_total' => (float) $payroll->net_total]
        );

        app(NotificationService::class)->notifyRoles(
            $payroll->company_id,
            ['hr_manager'],
            'payroll.approved',
            'Payroll disetujui',
            "Payroll {$payroll->period_label} telah disetujui dan siap disalurkan.",
            route('payroll.show', $payroll, false),
            ['payroll_id' => $payroll->id, 'period' => $payroll->period],
        );

        return back()->with('status', 'Payroll berhasil disetujui.');
    }

    public function reject(Request $request, Payroll $payroll): RedirectResponse
    {
        $this->authorizePayroll($request, $payroll, ['finance_manager', 'super_admin']);
        abort_if($payroll->status !== 'pending_approval', 422);
        $validated = $request->validate(['rejection_note' => ['required', 'string', 'max:2000']]);

        DB::transaction(function () use ($payroll, $validated): void {
            $payroll->transitionTo('needs_review', ['rejection_note' => $validated['rejection_note']]);
        });

        ActivityLogger::record($request, 'payroll', 'payroll.rejected',
            "Payroll {$payroll->period_label} dikembalikan untuk diperbaiki",
            'payroll', $payroll->id,
            ['period' => $payroll->period, 'rejection_note' => $validated['rejection_note']]
        );

        app(NotificationService::class)->notifyRoles(
            $payroll->company_id,
            ['hr_manager'],
            'payroll.rejected',
            'Payroll perlu diperbaiki',
            "Payroll {$payroll->period_label} dikembalikan oleh tim Finance.",
            route('payroll.show', $payroll, false),
            ['payroll_id' => $payroll->id, 'period' => $payroll->period],
        );

        return back()->with('status', 'Payroll dikembalikan ke HR.');
    }

    public function disburse(Request $request, Payroll $payroll): RedirectResponse
    {
        $this->authorizePayroll($request, $payroll, ['finance_manager', 'super_admin']);
        abort_if($payroll->status !== 'approved', 422);
        $request->validate(['proof' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120']]);
        $proof = $request->file('proof')?->store('disbursements', 'local');

        DB::transaction(function () use ($payroll, $request, $proof): void {
            $payroll->transitionTo('disbursed', ['disbursed_by' => $request->user()->id]);
            $payroll->update(array_filter(['disbursement_proof' => $proof]));
            $payroll->payrollItems()->update(['status' => 'transferred', 'disbursed_at' => now()]);
        });

        ActivityLogger::record($request, 'payroll', 'payroll.disbursed',
            "Payroll {$payroll->period_label} berhasil disalurkan",
            'payroll', $payroll->id,
            [
                'period' => $payroll->period,
                'employee_count' => $payroll->employee_count,
                'net_total' => (float) $payroll->net_total,
                'has_proof' => $proof !== null,
            ]
        );

        $employeeIds = $payroll->payrollItems()->pluck('employee_id');
        $employeeUsers = \App\Models\User::query()
            ->where('company_id', $payroll->company_id)
            ->where('status', 'active')
            ->whereIn('employee_id', $employeeIds)
            ->get(['id', 'company_id']);

        app(NotificationService::class)->notifyUsers(
            $employeeUsers,
            'payroll.disbursed',
            'Gaji telah dibayarkan',
            "Slip gaji {$payroll->period_label} sudah tersedia.",
            route('payroll.my-payslips', absolute: false),
            ['payroll_id' => $payroll->id, 'period' => $payroll->period],
        );

        app(NotificationService::class)->notifyRoles(
            $payroll->company_id,
            ['hr_manager'],
            'payroll.disbursed',
            'Payroll berhasil disalurkan',
            "{$payroll->employee_count} pembayaran {$payroll->period_label} berhasil diproses.",
            route('payroll.show', $payroll, false),
            ['payroll_id' => $payroll->id, 'period' => $payroll->period],
        );

        return back()->with('status', 'Payroll berhasil disburse.');
    }

    public function dispute(Request $request, Payroll $payroll): RedirectResponse
    {
        $this->authorizePayroll($request, $payroll, ['finance_manager', 'super_admin']);
        abort_if($payroll->status !== 'disbursed', 422);
        $validated = $request->validate(['dispute_reason' => ['required', 'string', 'max:2000']]);

        DB::transaction(function () use ($payroll, $validated, $request): void {
            $payroll->transitionTo('disputed', [
                'dispute_reason' => $validated['dispute_reason'],
                'disputed_by' => $request->user()->id,
            ]);
        });

        ActivityLogger::record($request, 'payroll', 'payroll.disputed',
            "Payroll {$payroll->period_label} ditandai disputed untuk revisi",
            'payroll', $payroll->id,
            ['period' => $payroll->period, 'reason' => $validated['dispute_reason']]
        );

        app(NotificationService::class)->notifyRoles(
            $payroll->company_id,
            ['hr_manager', 'finance_manager', 'super_admin'],
            'payroll.disputed',
            'Payroll butuh revisi',
            "Payroll {$payroll->period_label} ditandai untuk perbaikan setelah disburse.",
            route('payroll.show', $payroll, false),
            ['payroll_id' => $payroll->id, 'period' => $payroll->period],
        );

        return back()->with('status', 'Payroll ditandai untuk revisi (disputed).');
    }

    public function reprocess(Request $request, Payroll $payroll): RedirectResponse
    {
        $this->authorizePayroll($request, $payroll, ['hr_manager', 'finance_manager', 'super_admin']);
        abort_if($payroll->status !== 'disputed', 422);

        DB::transaction(function () use ($payroll, $request): void {
            $payroll->transitionTo('approved', ['reprocessed_by' => $request->user()->id]);
        });

        ActivityLogger::record($request, 'payroll', 'payroll.reprocessed',
            "Payroll {$payroll->period_label} diproses ulang dari disputed ke approved",
            'payroll', $payroll->id, ['period' => $payroll->period]
        );

        app(NotificationService::class)->notifyRoles(
            $payroll->company_id,
            ['hr_manager', 'finance_manager', 'super_admin'],
            'payroll.reprocessed',
            'Payroll siap disalurkan kembali',
            "Payroll {$payroll->period_label} telah diperbaiki dan siap disburse ulang.",
            route('payroll.show', $payroll, false),
            ['payroll_id' => $payroll->id, 'period' => $payroll->period],
        );

        return back()->with('status', 'Payroll berhasil diproses ulang ke status approved.');
    }

    public function reconcile(Request $request, Payroll $payroll): View
    {
        $this->authorizePayroll($request, $payroll, ['super_admin', 'finance_manager', 'hr_manager']);
        $transferredTotal = $payroll->payrollItems()->where('status', 'transferred')->sum('net_pay');

        return view('payflow.payroll.reconcile', compact('payroll', 'transferredTotal'));
    }

    /**
     * Download bulk transfer file (CSV) sesuai format bank yang dipilih.
     * Format yang didukung: generic (default), bca, mandiri, bni, bri
     */
    public function downloadTransferFile(Request $request, Payroll $payroll)
    {
        $this->authorizePayroll($request, $payroll, ['finance_manager', 'super_admin']);
        abort_if(! in_array($payroll->status, ['approved', 'disbursed', 'disputed'], true), 422);

        $format = in_array($request->input('format'), ['generic', 'bca', 'mandiri', 'bni', 'bri'], true)
            ? $request->input('format')
            : 'generic';

        $payroll->load('payrollItems.employee');
        $items    = $payroll->payrollItems;
        $filename = 'transfer-' . $payroll->period . '-' . $format . '.csv';

        return response()->streamDownload(function () use ($items, $payroll, $format): void {
            $handle = fopen('php://output', 'wb');

            match ($format) {
                'bca' => $this->writeBcaFormat($handle, $items, $payroll),
                'mandiri' => $this->writeMandiriFormat($handle, $items, $payroll),
                'bni' => $this->writeBniFormat($handle, $items, $payroll),
                'bri' => $this->writeBriFormat($handle, $items, $payroll),
                default => $this->writeGenericFormat($handle, $items, $payroll),
            };

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Format generik — bisa diimport ke semua internet banking */
    private function writeGenericFormat($handle, $items, Payroll $payroll): void
    {
        fputcsv($handle, ['# PaySync Bulk Transfer File']);
        fputcsv($handle, ['# Periode: ' . $payroll->period_label]);
        fputcsv($handle, ['# Total Karyawan: ' . $payroll->employee_count]);
        fputcsv($handle, ['# Total Net Pay: ' . number_format((float) $payroll->net_total, 2, '.', '')]);
        fputcsv($handle, ['# Dibuat: ' . now()->format('d/m/Y H:i')]);
        fputcsv($handle, []);
        fputcsv($handle, ['No', 'Nama Karyawan', 'NIP', 'Nama Bank', 'No. Rekening', 'Nominal', 'Keterangan']);

        $no = 1;
        foreach ($items as $item) {
            fputcsv($handle, [
                $no++,
                $item->employee?->name ?? '-',
                $item->employee?->nip ?? '-',
                $item->employee?->bank_name ?? '-',
                $item->employee?->bank_account_number ?? '-',
                number_format((float) $item->net_pay, 2, '.', ''),
                'Gaji ' . $payroll->period_label,
            ]);
        }

        fputcsv($handle, []);
        fputcsv($handle, ['', '', '', '', 'TOTAL', number_format((float) $items->sum('net_pay'), 2, '.', ''), '']);
    }

    /** Format BCA Bulk Payment (KlikBCA Bisnis) */
    private function writeBcaFormat($handle, $items, Payroll $payroll): void
    {
        // BCA format: No, Nama, No Rekening, Nominal, Berita
        fputcsv($handle, ['No', 'Nama Penerima', 'Nomor Rekening', 'Nominal', 'Berita Transfer']);
        foreach ($items as $item) {
            fputcsv($handle, [
                $item->employee?->nip ?? '-',
                $item->employee?->name ?? '-',
                $item->employee?->bank_account_number ?? '-',
                number_format((float) $item->net_pay, 0, '', ''),
                'GAJI ' . strtoupper($payroll->period_label),
            ]);
        }
    }

    /** Format Mandiri Cash Management */
    private function writeMandiriFormat($handle, $items, Payroll $payroll): void
    {
        // Mandiri format: sequence, beneficiary name, account no, amount, currency, remark
        fputcsv($handle, ['SEQ', 'BENEFICIARY_NAME', 'ACCOUNT_NUMBER', 'AMOUNT', 'CURRENCY', 'REMARK']);
        $seq = 1;
        foreach ($items as $item) {
            fputcsv($handle, [
                str_pad($seq++, 5, '0', STR_PAD_LEFT),
                strtoupper($item->employee?->name ?? '-'),
                $item->employee?->bank_account_number ?? '-',
                number_format((float) $item->net_pay, 2, '.', ''),
                'IDR',
                'SALARY ' . $payroll->period,
            ]);
        }
    }

    /** Format BNI Direct Debit / Payroll */
    private function writeBniFormat($handle, $items, Payroll $payroll): void
    {
        fputcsv($handle, ['NO', 'NAMA', 'NO_REKENING', 'NOMINAL', 'KETERANGAN']);
        $no = 1;
        foreach ($items as $item) {
            fputcsv($handle, [
                $no++,
                $item->employee?->name ?? '-',
                $item->employee?->bank_account_number ?? '-',
                number_format((float) $item->net_pay, 0, '', ''),
                'GAJI ' . $payroll->period_label,
            ]);
        }
    }

    /** Format BRI Payroll */
    private function writeBriFormat($handle, $items, Payroll $payroll): void
    {
        fputcsv($handle, ['NOMOR', 'NAMA_PENERIMA', 'REKENING', 'JUMLAH', 'BERITA']);
        $no = 1;
        foreach ($items as $item) {
            fputcsv($handle, [
                $no++,
                $item->employee?->name ?? '-',
                $item->employee?->bank_account_number ?? '-',
                number_format((float) $item->net_pay, 0, '', ''),
                'GAJI ' . strtoupper($payroll->period_label),
            ]);
        }
    }

    public function payslip(Request $request, Payroll $payroll, Employee $employee): View
    {
        $this->authorizePayroll($request, $payroll, ['super_admin', 'hr_manager', 'finance_manager', 'employee']);
        abort_if($payroll->company_id !== $employee->company_id || ! in_array($payroll->status, ['approved', 'disbursed'], true), 404);
        if ($request->user()->isEmployee()) {
            abort_if($request->user()->employee_id !== $employee->id, 403);
        }
        $item = $payroll->payrollItems()->where('employee_id', $employee->id)->firstOrFail();

        return view('payflow.payroll.payslip', compact('payroll', 'employee', 'item'));
    }

    public function myPayslips(Request $request): View
    {
        $user = $request->user();
        abort_if(! $user->isEmployee() || ! $user->employee_id, 403);
        $items = PayrollItem::with('payroll')->where('employee_id', $user->employee_id)->where('company_id', $user->company_id)
            ->whereHas('payroll', fn ($q) => $q->whereIn('status', ['approved', 'disbursed']))->get()
            ->sortByDesc(fn ($item) => $item->payroll->period);

        return view('payflow.payroll.my-payslips', compact('items'));
    }

    /** Backwards-compatible endpoint used by the existing payroll page. */
    public function store(Request $request, Payroll $payroll): RedirectResponse
    {
        return $this->calculate($request, $payroll, app(PayrollService::class));
    }

    private function authorizePayroll(Request $request, Payroll $payroll, array $roles): void
    {
        abort_if(! $request->user()->hasAnyRole($roles), 403);
        abort_if($request->user()->company_id === null || $payroll->company_id !== $request->user()->company_id, 404);
    }
}
