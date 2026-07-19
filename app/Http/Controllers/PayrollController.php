<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Services\AttendanceCsvImporter;
use App\Services\PayrollService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollController extends Controller
{
    private const ALLOWED_STATUSES = ['draft', 'needs_review', 'pending_approval', 'approved', 'disbursed'];

    private const ALLOWED_SORT_COLUMNS = ['period', 'employee_count', 'gross_total', 'net_total', 'status'];

    private const ALLOWED_PER_PAGE = [15, 30, 50];

    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        abort_if(! $user->hasAnyRole(['super_admin', 'hr_manager', 'finance_manager']) && $user->company_id !== null, 403);
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
        $payroll = Payroll::create([
            'company_id' => $user->company_id, 'period' => $validated['period'], 'period_label' => $validated['period_label'],
            'status' => 'draft', 'employee_count' => 0, 'gross_total' => 0, 'deduction_total' => 0, 'net_total' => 0,
        ]);

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

    public function importAttendance(Request $request, Payroll $payroll): View
    {
        $this->authorizePayroll($request, $payroll, ['hr_manager']);
        abort_if(! in_array($payroll->status, ['draft', 'needs_review'], true), 422);

        return view('payflow.payroll.attendance-import', compact('payroll'));
    }

    public function storeAttendance(Request $request, Payroll $payroll, AttendanceCsvImporter $importer): RedirectResponse
    {
        $this->authorizePayroll($request, $payroll, ['hr_manager']);
        abort_if(! in_array($payroll->status, ['draft', 'needs_review'], true), 422);
        $request->validate(['file' => ['required', 'file', 'extensions:csv', 'mimes:csv,txt', 'max:2048']]);
        $result = $importer->import($request->file('file'), $payroll);
        if (! $result->successful()) {
            return back()->with('import_errors', $result->errors);
        }

        return redirect()->route('payroll.show', $payroll)->with('status', $result->imported.' record kehadiran berhasil diimport.');
    }

    public function downloadAttendanceTemplate(Request $request)
    {
        abort_if(! $request->user()->hasAnyRole(['super_admin', 'hr_manager', 'finance_manager']), 403);

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, ['nip', 'days_present', 'overtime_hours', 'leave_days']);
            fputcsv($handle, ['EMP-0001', 22, 0, 0]);
            fclose($handle);
        }, 'attendance-template.csv', ['Content-Type' => 'text/csv']);
    }

    public function calculate(Request $request, Payroll $payroll, PayrollService $service): RedirectResponse
    {
        $this->authorizePayroll($request, $payroll, ['hr_manager']);
        abort_if($payroll->status !== 'draft', 422);
        $service->calculate($payroll);

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
        $payroll->transitionTo('pending_approval', ['submitted_by' => $request->user()->id]);

        return back()->with('status', 'Payroll berhasil diajukan untuk persetujuan.');
    }

    public function approve(Request $request, Payroll $payroll): RedirectResponse
    {
        $this->authorizePayroll($request, $payroll, ['finance_manager']);
        abort_if($payroll->status !== 'pending_approval', 422);
        $payroll->transitionTo('approved', ['approved_by' => $request->user()->id]);

        return back()->with('status', 'Payroll berhasil disetujui.');
    }

    public function reject(Request $request, Payroll $payroll): RedirectResponse
    {
        $this->authorizePayroll($request, $payroll, ['finance_manager']);
        abort_if($payroll->status !== 'pending_approval', 422);
        $validated = $request->validate(['rejection_note' => ['required', 'string', 'max:2000']]);
        $payroll->transitionTo('needs_review', ['rejection_note' => $validated['rejection_note']]);

        return back()->with('status', 'Payroll dikembalikan ke HR.');
    }

    public function disburse(Request $request, Payroll $payroll): RedirectResponse
    {
        $this->authorizePayroll($request, $payroll, ['finance_manager']);
        abort_if($payroll->status !== 'approved', 422);
        $request->validate(['proof' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120']]);
        $proof = $request->file('proof')?->store('disbursements', 'local');
        $payroll->transitionTo('disbursed', ['disbursed_by' => $request->user()->id]);
        $payroll->update(array_filter(['disbursement_proof' => $proof]));
        $payroll->payrollItems()->update(['status' => 'transferred', 'disbursed_at' => now()]);

        return back()->with('status', 'Payroll berhasil disburse.');
    }

    public function reconcile(Request $request, Payroll $payroll): View
    {
        $this->authorizePayroll($request, $payroll, ['super_admin', 'finance_manager']);
        $transferredTotal = $payroll->payrollItems()->where('status', 'transferred')->sum('net_pay');

        return view('payflow.payroll.reconcile', compact('payroll', 'transferredTotal'));
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
