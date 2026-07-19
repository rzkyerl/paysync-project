<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\EmployeeCsvImporter;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    /**
     * Allowed sort columns — whitelist to prevent SQL injection via column name.
     */
    private const ALLOWED_SORT_COLUMNS = ['name', 'nip', 'department', 'work_status', 'join_date'];

    /**
     * Allowed per-page values.
     */
    private const ALLOWED_PER_PAGE = [15, 30, 50];

    public function import(Request $request): View
    {
        abort_if(! $request->user()->isHrManager(), 403);

        return view('payflow.employees.import');
    }

    public function importStore(Request $request, EmployeeCsvImporter $importer): RedirectResponse
    {
        abort_if(! $request->user()->isHrManager(), 403);

        $request->validate(['file' => ['required', 'file', 'extensions:csv', 'mimes:csv,txt', 'max:2048']]);
        $result = $importer->import($request->file('file'), (int) $request->user()->company_id);

        if (! $result->successful()) {
            return back()->withInput()->with('import_errors', $result->errors);
        }

        session()->flash('toast', ['type' => 'success', 'message' => $result->imported.' karyawan berhasil diimport.']);

        return redirect()->route('employees.index');
    }

    public function downloadTemplate(Request $request)
    {
        abort_if(! $request->user()->isHrManager(), 403);

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, ['nip', 'name', 'department', 'position', 'work_status', 'join_date', 'basic_salary', 'bank_name', 'bank_account_number']);
            fputcsv($handle, ['EMP-0001', 'Nama Karyawan', 'People', 'HR Staff', 'active', now()->format('Y-m-d'), '5000000', 'BCA', '1234567890']);
            fclose($handle);
        }, 'employee-import-template.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Display a paginated, searchable, filterable, sortable list of employees.
     * HR, finance, and super admin can read employee records.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        abort_if(! $user->hasAnyRole(['super_admin', 'hr_manager', 'finance_manager']) && $user->company_id !== null, 403);

        // Validate and sanitize sort parameters
        $sort = in_array($request->sort, self::ALLOWED_SORT_COLUMNS, true) ? $request->sort : 'name';
        $dir = in_array($request->dir, ['asc', 'desc'], true) ? $request->dir : 'asc';
        $perPage = in_array((int) $request->per_page, self::ALLOWED_PER_PAGE, true)
            ? (int) $request->per_page
            : 15;

        $employees = Employee::query()
            ->when($user->company_id, fn ($q, $companyId) => $q->where('company_id', $companyId))
            ->when(! $user->company_id && $user->role === 'employee', fn ($q) => $q->whereRaw('1 = 0'))
            ->when(! $user->company_id && $user->role !== 'employee', fn ($q) => $q->whereNull('company_id'))
            ->when($request->search, fn ($q, $s) => $q->where(fn ($searchQuery) => $searchQuery
                ->where('name', 'like', "%{$s}%")
                ->orWhere('nip', 'like', "%{$s}%")))
            ->when($request->department, fn ($q, $d) => $q->where('department', $d))
            ->when($request->status, fn ($q, $s) => $q->where('work_status', $s))
            ->orderBy($sort, $dir)
            ->paginate($perPage)
            ->withQueryString();

        // Redirect to page 1 if requested page exceeds last page
        if ($employees->currentPage() > $employees->lastPage()) {
            return redirect()->route('employees.index', array_merge(
                $request->except('page'),
                ['page' => 1]
            ));
        }

        // Fetch distinct departments for the filter dropdown
        $departments = Employee::query()
            ->when($user->company_id, fn ($q, $companyId) => $q->where('company_id', $companyId))
            ->when(! $user->company_id && $user->role === 'employee', fn ($q) => $q->whereRaw('1 = 0'))
            ->when(! $user->company_id && $user->role !== 'employee', fn ($q) => $q->whereNull('company_id'))
            ->select('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        return view('payflow.app', [
            'page' => 'employees',
            'employees' => $employees,
            'departments' => $departments,
            'sortBy' => $sort,
            'sortDir' => $dir,
            'perPage' => $perPage,
            'isDemoUser' => $user->isDemoUser(),
            'isEmpty' => $employees->total() === 0 && ! $request->hasAny(['search', 'department', 'status']),
            'companyName' => $user->company?->name ?? 'Workspace',
        ]);
    }

    /**
     * Show the form for creating a new employee.
     * Read access is available to HR, finance, and super admin. The policy
     * enforces the company boundary for the individual record.
     */
    public function create(Request $request): View
    {
        abort_if($request->user()->role !== 'hr_manager', 403);

        $departments = Employee::query()
            ->select('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        return view('payflow.app', [
            'page' => 'employee-create',
            'departments' => $departments,
            'employee' => null,
        ]);
    }

    /**
     * Store a newly created employee.
     * Only accessible to hr_manager role.
     */
    public function store(Request $request): RedirectResponse
    {
        abort_if($request->user()->role !== 'hr_manager', 403);

        $validated = $request->validate([
            'nip' => ['required', 'string', 'max:20', 'unique:employees,nip', 'regex:/^EMP-\d+$/'],
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'department' => ['required', 'string', 'max:100'],
            'position' => ['required', 'string', 'max:100'],
            'work_status' => ['required', Rule::in(['active', 'probation', 'contract', 'inactive'])],
            'join_date' => ['required', 'date'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'bank_account_status' => ['required', Rule::in(['verified', 'unverified', 'rejected'])],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:100'],
        ]);

        $validated['company_id'] = $request->user()->company_id;
        Employee::create($validated);

        session()->flash('toast', [
            'type' => 'success',
            'message' => 'Karyawan berhasil ditambahkan.',
        ]);

        return redirect()->route('employees.index');
    }

    /**
     * Display a single employee's details.
     * Only accessible to hr_manager role.
     */
    public function show(Request $request, Employee $employee): View
    {
        Gate::authorize('view', $employee);

        return view('payflow.app', [
            'page' => 'employee-show',
            'employee' => $employee,
            'canEdit' => $request->user()->isHrManager(),
            'canVerifyBank' => $request->user()->isHrManager(),
        ]);
    }

    /**
     * Show the form for editing an existing employee.
     * Only accessible to hr_manager role.
     */
    public function edit(Request $request, Employee $employee): View
    {
        Gate::authorize('update', $employee);

        $departments = Employee::query()
            ->where('company_id', $request->user()->company_id)
            ->select('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        return view('payflow.app', [
            'page' => 'employee-edit',
            'employee' => $employee,
            'departments' => $departments,
        ]);
    }

    /**
     * Update an existing employee.
     * Only accessible to hr_manager role.
     */
    public function update(Request $request, Employee $employee): RedirectResponse
    {
        Gate::authorize('update', $employee);

        $validated = $request->validate([
            'nip' => ['required', 'string', 'max:20', Rule::unique('employees', 'nip')->ignore($employee->id), 'regex:/^EMP-\d+$/'],
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'department' => ['required', 'string', 'max:100'],
            'position' => ['required', 'string', 'max:100'],
            'work_status' => ['required', Rule::in(['active', 'probation', 'contract', 'inactive'])],
            'join_date' => ['required', 'date'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'bank_account_status' => ['required', Rule::in(['verified', 'unverified', 'rejected'])],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:100'],
        ]);

        if ($validated['bank_account_number'] !== $employee->bank_account_number
            || $validated['bank_name'] !== $employee->bank_name) {
            $validated['bank_account_status'] = 'unverified';
        }

        $employee->update($validated);

        session()->flash('toast', [
            'type' => 'success',
            'message' => 'Data karyawan berhasil diperbarui.',
        ]);

        return redirect()->route('employees.index');
    }

    /**
     * Soft-delete an employee.
     * Only accessible to hr_manager role.
     * Catches FK constraint violations and returns an error toast instead.
     */
    public function destroy(Request $request, Employee $employee): RedirectResponse
    {
        Gate::authorize('delete', $employee);

        try {
            $employee->delete();

            session()->flash('toast', [
                'type' => 'success',
                'message' => 'Karyawan berhasil dihapus.',
            ]);
        } catch (QueryException $e) {
            session()->flash('toast', [
                'type' => 'error',
                'message' => 'Karyawan tidak dapat dihapus karena memiliki data terkait (payroll atau rekening aktif).',
            ]);
        }

        return redirect()->back();
    }

    public function verifyBank(Request $request, Employee $employee): RedirectResponse
    {
        Gate::authorize('update', $employee);
        $employee->update(['bank_account_status' => 'verified']);

        session()->flash('toast', ['type' => 'success', 'message' => 'Rekening karyawan berhasil diverifikasi.']);

        return back();
    }

    public function rejectBank(Request $request, Employee $employee): RedirectResponse
    {
        Gate::authorize('update', $employee);
        $employee->update(['bank_account_status' => 'rejected']);

        session()->flash('toast', ['type' => 'warning', 'message' => 'Rekening karyawan ditolak.']);

        return back();
    }
}
